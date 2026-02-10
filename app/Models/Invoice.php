<?php

namespace App\Models;

use Billmora;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate invoice_number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate invoice number based on settings.
     *
     * @return string
     */
    public static function generateInvoiceNumber(): string
    {
        $format = Billmora::getGeneral('invoice_number_format');
        $padding = (int) Billmora::getGeneral('invoice_number_padding');
        $increment = (int) Billmora::getGeneral('invoice_number_increment');

        $lastInvoice = static::whereNotNull('invoice_number')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice && preg_match('/(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $lastNumber = (int) end($matches);
            $nextNumber = $lastNumber + $increment;
        } else {
            $nextNumber = $increment;
        }

        $paddedNumber = str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);

        $invoiceNumber = str_replace(
            ['{number}', '{day}', '{month}', '{year}'],
            [
                $paddedNumber,
                date('d'),
                date('m'),
                date('Y'),
            ],
            $format
        );

        $counter = 0;
        $originalNumber = $invoiceNumber;
        while (static::where('invoice_number', $invoiceNumber)->exists()) {
            $counter++;
            $invoiceNumber = $originalNumber . '-' . $counter;
        }

        return $invoiceNumber;
    }

    /**
     * Get the user that owns this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the service associated with the invoice through invoice item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function service()
    {
        return $this->hasOneThrough(
            Service::class,
            InvoiceItem::class,
            'invoice_id',
            'id',
            'id',
            'service_id'
        );
    }

    /**
     * Get the items for this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope a query to only include unpaid invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    /**
     * Scope a query to only include paid invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Mark the invoice as paid with optional payment details.
     *
     * @param string|null $paymentMethod
     * @param string|null $transactionId
     * @return bool
     */
    public function markAsPaid(?string $paymentMethod = null, ?string $transactionId = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
        ]);
    }
}
