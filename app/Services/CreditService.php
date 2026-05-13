<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Str;

class CreditService
{
    use AuditsSystem;

    /**
     * Attempt to automatically pay an invoice using the owner's credit balance.
     *
     * Returns true if the invoice was fully settled, false otherwise.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function attemptAutoPayment(Invoice $invoice): bool
    {
        // Guard: feature must be enabled globally
        if (!(bool) Billmora::getGeneral('credit_auto_payment')) {
            return false;
        }

        // Guard: invoice must be unpaid with a positive balance due
        if ($invoice->status !== 'unpaid' || $invoice->amount_due <= 0) {
            return false;
        }

        // Guard: Credit Deposit invoices must not be auto-paid (prevents loop)
        $isCreditDeposit = $invoice->items()->where('description', 'like', 'Credit Deposit%')->exists();
        if ($isCreditDeposit) {
            return false;
        }

        $invoice->loadMissing('user');
        $user = $invoice->user;

        // Guard: user must have opted-in to auto credit payment
        if (!$user || !$user->auto_credit_payment) {
            return false;
        }

        $wallet = $user->getCreditWallet($invoice->currency);

        // Guard: user must have sufficient credit balance to fully cover the invoice
        if ($wallet->balance < $invoice->amount_due) {
            return false;
        }

        $settled = false;

        DB::transaction(function () use ($invoice, $wallet, &$settled) {
            $currentAmountDue = $invoice->amount_due;
            $amountToApply = min($wallet->balance, $currentAmountDue);

            $wallet->removeCredit($amountToApply);

            $transaction = Transaction::create([
                'user_id'     => $invoice->user_id,
                'invoice_id'  => $invoice->id,
                'plugin_id'   => null,
                'reference'   => 'AUTOCREDIT-' . strtoupper(Str::random(10)),
                'description' => 'Auto Credit Payment Applied',
                'currency'    => $invoice->currency,
                'amount'      => $amountToApply,
                'fee'         => 0,
            ]);

            $this->recordSystem('transaction.created', $transaction->toArray(), 'auto_credit');

            $remainingDue = $currentAmountDue - $amountToApply;

            if ($remainingDue <= 0) {
                $invoice->status = 'paid';
                $invoice->paid_at = now();
                $invoice->save();

                $this->recordSystem('invoice.paid', $invoice->toArray(), 'auto_credit');

                $settled = true;
            }
        });

        if ($settled) {
            Log::info("AutoCreditPayment: Invoice {$invoice->invoice_number} fully settled for User ID {$invoice->user_id}");
        } else {
            Log::info("AutoCreditPayment: Partial credit applied to Invoice {$invoice->invoice_number} for User ID {$invoice->user_id}");
        }

        return $settled;
    }
}
