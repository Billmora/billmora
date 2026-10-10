<?php

namespace Plugins\Modules\Affiliate;

use App\Contracts\ModuleInterface;
use App\Events\Invoice\Paid as InvoicePaid;
use App\Events\User\Registered as UserRegistered;
use App\Support\AbstractPlugin;
use Illuminate\Support\Facades\Cookie;
use Plugins\Modules\Affiliate\Models\AffiliateCommission;
use Plugins\Modules\Affiliate\Models\AffiliateMember;
use Plugins\Modules\Affiliate\Models\AffiliateReferral;

class AffiliateModule extends AbstractPlugin implements ModuleInterface
{
    public function getConfigSchema(): array
    {
        $defaultCurrency = \App\Models\Currency::where('is_default', true)->value('code');

        return [
            'commission_type' => [
                'type'    => 'select',
                'label'   => __('admin/affiliate.config_schema.commission_type.label'),
                'helper'  => __('admin/affiliate.config_schema.commission_type.helper', ['currency' => $defaultCurrency]),
                'options' => [
                    'percentage' => __('admin/affiliate.config_schema.commission_type.options.percentage'),
                    'fixed'      => __('admin/affiliate.config_schema.commission_type.options.fixed', ['currency' => $defaultCurrency]),
                ],
                'rules'   => 'required|in:percentage,fixed',
                'default' => 'percentage',
            ],
            'commission_value' => [
                'type'    => 'text',
                'label'   => __('admin/affiliate.config_schema.commission_value.label'),
                'helper'  => __('admin/affiliate.config_schema.commission_value.helper', ['currency' => $defaultCurrency]),
                'rules'   => 'required|numeric|min:0',
                'default' => '10',
            ],
            'commission_trigger' => [
                'type'    => 'select',
                'label'   => __('admin/affiliate.config_schema.commission_trigger.label'),
                'helper'  => __('admin/affiliate.config_schema.commission_trigger.helper'),
                'options' => [
                    'first_order' => __('admin/affiliate.config_schema.commission_trigger.options.first_order'),
                    'every_order' => __('admin/affiliate.config_schema.commission_trigger.options.every_order'),
                ],
                'rules'   => 'required|in:first_order,every_order',
                'default' => 'first_order',
            ],
            'referral_scope' => [
                'type'    => 'select',
                'label'   => __('admin/affiliate.config_schema.referral_scope.label'),
                'helper'  => __('admin/affiliate.config_schema.referral_scope.helper'),
                'options' => [
                    'new_users_only' => __('admin/affiliate.config_schema.referral_scope.options.new_users_only'),
                    'all_users'      => __('admin/affiliate.config_schema.referral_scope.options.all_users'),
                ],
                'rules'   => 'required|in:new_users_only,all_users',
                'default' => 'new_users_only',
            ],
            'cookie_lifetime_days' => [
                'type'    => 'text',
                'label'   => __('admin/affiliate.config_schema.cookie_lifetime_days.label'),
                'helper'  => __('admin/affiliate.config_schema.cookie_lifetime_days.helper'),
                'rules'   => 'required|integer|min:1',
                'default' => '30',
            ],
            'min_withdrawal' => [
                'type'    => 'text',
                'label'   => __('admin/affiliate.config_schema.min_withdrawal.label'),
                'helper'  => __('admin/affiliate.config_schema.min_withdrawal.helper', ['currency' => $defaultCurrency]),
                'rules'   => 'required|numeric|min:0',
                'default' => '50000',
            ],
            'auto_approve_commission' => [
                'type'    => 'select',
                'label'   => __('admin/affiliate.config_schema.auto_approve_commission.label'),
                'helper'  => __('admin/affiliate.config_schema.auto_approve_commission.helper'),
                'options' => [
                    '0' => __('admin/affiliate.config_schema.auto_approve_commission.options.0'),
                    '1' => __('admin/affiliate.config_schema.auto_approve_commission.options.1'),
                ],
                'rules'   => 'required|in:0,1',
                'default' => '0',
            ],
        ];
    }

    public function getPermissions(): array
    {
        return [
            'modules.affiliate.view',
            'modules.affiliate.manage',
        ];
    }

    public function getNavigationAdmin(): array
    {
        return [
            'affiliate' => [
                'label'      => __('admin/affiliate.title'),
                'icon'       => 'lucide-handshake',
                'route'      => route('admin.modules.affiliate.index'),
                'permission' => 'modules.affiliate.manage',
            ],
        ];
    }

    public function getNavigationClient(): array
    {
        return [
            'affiliate' => [
                'label' => __('client/affiliate.title'),
                'icon'  => 'lucide-handshake',
                'route' => route('client.modules.affiliate.index'),
                'auth'  => true,
            ],
        ];
    }

    public function getSubscribedEvents(): array
    {
        return [
            UserRegistered::class            => 'onUserRegistered',
            InvoicePaid::class               => 'onInvoicePaid',
            \App\Events\Order\Created::class => 'onOrderCreated',
        ];
    }

    /**
     * Track referral when a new user registers.
     */
    public function onUserRegistered(UserRegistered $event): void
    {
        $user = $event->user;
        $this->processReferralCookieForUser($user->id);
    }

    /**
     * Track referral when an existing user places an order.
     * This acts as a reliable fallback for existing users, ensuring the referral
     * is locked in right before the invoice is paid.
     */
    public function onOrderCreated(\App\Events\Order\Created $event): void
    {
        $user = $event->order->user;
        if (!$user) {
            return;
        }

        $scope = $this->getInstanceConfig('referral_scope', 'new_users_only');
        if ($scope === 'all_users') {
            $this->processReferralCookieForUser($user->id);
        }
    }

    /**
     * Process the affiliate_ref cookie and create a referral record if valid.
     */
    private function processReferralCookieForUser(int $userId): void
    {
        $referralCode = Cookie::get('affiliate_ref');

        if (empty($referralCode)) {
            return;
        }

        $member = AffiliateMember::where('referral_code', $referralCode)
            ->where('status', 'active')
            ->first();

        if (!$member) {
            return;
        }

        // Prevent self-referral
        if ($member->user_id === $userId) {
            return;
        }

        // Prevent duplicate referral
        if (AffiliateReferral::where('referred_user_id', $userId)->exists()) {
            return;
        }

        AffiliateReferral::create([
            'affiliate_member_id' => $member->id,
            'referred_user_id'    => $userId,
        ]);

        Cookie::queue(Cookie::forget('affiliate_ref'));
    }

    /**
     * Award commission when an invoice is paid.
     */
    public function onInvoicePaid(InvoicePaid $event): void
    {
        $invoice = $event->invoice;

        $referral = AffiliateReferral::where('referred_user_id', $invoice->user_id)->first();

        if (!$referral) {
            return;
        }

        $member = $referral->member;

        if (!$member || $member->status !== 'active') {
            return;
        }

        $trigger = $this->getInstanceConfig('commission_trigger', 'first_order');

        // For first_order: skip if already converted
        if ($trigger === 'first_order' && $referral->converted) {
            return;
        }

        // For every_order: skip if this invoice already has a commission
        if ($trigger === 'every_order') {
            $exists = AffiliateCommission::where('referral_id', $referral->id)
                ->where('invoice_id', $invoice->id)
                ->exists();

            if ($exists) {
                return;
            }
        }

        $autoApprove = (bool) $this->getInstanceConfig('auto_approve_commission', false);

        ['amount' => $commissionAmount, 'currency' => $commissionCurrency] = $this->calculateCommission($invoice);

        if ($commissionAmount <= 0) {
            return;
        }

        AffiliateCommission::create([
            'affiliate_member_id' => $member->id,
            'referral_id'         => $referral->id,
            'invoice_id'          => $invoice->id,
            'amount'              => $commissionAmount,
            'currency'            => $commissionCurrency,
            'status'              => $autoApprove ? 'approved' : 'pending',
        ]);

        if ($autoApprove) {
            $member->increment('balance', $commissionAmount);
            $member->increment('total_earned', $commissionAmount);
        }

        // Mark as converted (for first_order trigger)
        if (!$referral->converted) {
            $referral->update([
                'converted'    => true,
                'converted_at' => now(),
            ]);
        }
    }

    /**
     * Calculate commission amount and currency from an invoice.
     *
     * For "percentage" type: commission is calculated as a % of invoice->total,
     * and stored in the same currency as the invoice.
     *
     * For "fixed" type: commission is a flat amount always stored in the
     * default system currency (regardless of the invoice currency).
     *
     * @param  \App\Models\Invoice  $invoice
     * @return array{amount: float, currency: string}
     */
    private function calculateCommission($invoice): array
    {
        $type  = $this->getInstanceConfig('commission_type', 'percentage');
        $value = (float) $this->getInstanceConfig('commission_value', 10);
        
        $defaultCode = \App\Models\Currency::where('is_default', true)->value('code');

        if ($type === 'percentage') {
            $percentageAmount = ($value / 100) * (float) $invoice->total;
            
            // Convert to default currency if the invoice is in a different currency
            if ($invoice->currency !== $defaultCode) {
                $invoiceCurrency = \App\Models\Currency::where('code', $invoice->currency)->first();
                $rate = $invoiceCurrency && $invoiceCurrency->base_rate > 0 ? (float) $invoiceCurrency->base_rate : 1;
                $percentageAmount = $percentageAmount / $rate;
            }

            return [
                'amount'   => round($percentageAmount, 2),
                'currency' => $defaultCode,
            ];
        }

        // Fixed amount — always stored in the default system currency
        return [
            'amount'   => $value,
            'currency' => $defaultCode,
        ];
    }

    /**
     * Register referral cookie middleware.
     */
    protected function setup(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', \Plugins\Modules\Affiliate\Http\Middleware\TrackReferral::class);
    }
}
