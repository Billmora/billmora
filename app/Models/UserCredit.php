<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCredit extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * Get the user that owns the credit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add the specified amount to the user's credit balance and record the audit log.
     *
     * @param  float  $amount
     * @param  string  $description
     * @param  string|null  $relatedType
     * @param  int|null  $relatedId
     * @return self
     */
    public function addCredit(float $amount, string $description, ?string $relatedType = null, ?int $relatedId = null): self
    {
        if ($amount <= 0) return $this;

        $this->balance += $amount;
        $this->save();

        $this->recordCreditAudit('credit.added', $amount, $description, $relatedType, $relatedId);

        return $this;
    }

    /**
     * Deduct the specified amount from the user's credit balance and record the audit log.
     *
     * @param  float  $amount
     * @param  string  $description
     * @param  string|null  $relatedType
     * @param  int|null  $relatedId
     * @return self
     *
     * @throws \Exception
     */
    public function removeCredit(float $amount, string $description, ?string $relatedType = null, ?int $relatedId = null): self
    {
        if ($amount <= 0) return $this;
        
        if ($this->balance < $amount) {
            throw new \Exception("Insufficient credit balance in {$this->currency} wallet.");
        }

        $this->balance -= $amount;
        $this->save();

        $this->recordCreditAudit('credit.removed', $amount, $description, $relatedType, $relatedId);

        return $this;
    }
}
