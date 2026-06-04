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

        // Guard: optimistic early-exit — re-checked inside the transaction after locking.
        if ($wallet->balance < $invoice->amount_due) {
            return false;
        }

        $settled = false;

        DB::transaction(function () use ($invoice, $wallet, &$settled) {
            // Re-acquire both rows with exclusive locks to prevent double-debit
            // when a concurrent request (e.g. manual settle) runs at the same time.
            $lockedWallet  = \App\Models\UserCredit::where('id', $wallet->id)->lockForUpdate()->first();
            $lockedInvoice = \App\Models\Invoice::where('id', $invoice->id)->lockForUpdate()->first();

            // Abort if rows are gone or the invoice was already settled by a concurrent request.
            if (!$lockedWallet || !$lockedInvoice || $lockedInvoice->status !== 'unpaid') {
                return;
            }

            $currentAmountDue = $lockedInvoice->amount_due;

            // Re-validate balance with the locked, up-to-date wallet value.
            if ($lockedWallet->balance < $currentAmountDue) {
                return;
            }

            $amountToApply = min($lockedWallet->balance, $currentAmountDue);

            $lockedWallet->removeCredit($amountToApply);

            $transaction = Transaction::create([
                'user_id'     => $lockedInvoice->user_id,
                'invoice_id'  => $lockedInvoice->id,
                'plugin_id'   => null,
                'reference'   => 'AUTOCREDIT-' . strtoupper(Str::random(10)),
                'description' => 'Auto Credit Payment Applied',
                'currency'    => $lockedInvoice->currency,
                'amount'      => $amountToApply,
                'fee'         => 0,
            ]);

            $this->recordSystem('transaction.created', $transaction->toArray(), 'auto_credit');

            $remainingDue = $currentAmountDue - $amountToApply;

            if ($remainingDue <= 0) {
                $lockedInvoice->status  = 'paid';
                $lockedInvoice->paid_at = now();
                $lockedInvoice->save();

                $this->recordSystem('invoice.paid', $lockedInvoice->toArray(), 'auto_credit');

                $settled = true;
            }
        });

        if ($settled) {
            Log::info("AutoCreditPayment: Invoice {$invoice->invoice_number} fully settled for User ID {$invoice->user_id}");
        } else {
            Log::info("AutoCreditPayment: Insufficient balance or already settled — skipped Invoice {$invoice->invoice_number} for User ID {$invoice->user_id}");
        }

        return $settled;
    }
}
