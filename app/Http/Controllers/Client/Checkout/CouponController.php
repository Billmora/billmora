<?php

namespace App\Http\Controllers\Client\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Services\Checkout\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CouponController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
        // 
    }

    /**
     * Validate and apply a coupon code to the current cart session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function check(Request $request)
    {
        $request->validate([
            'coupon_code' => ['required', 'string'],
        ]);

        $cartItems = $this->cartService->getItems();

        if (empty($cartItems)) {
            return redirect()->route('client.checkout.cart')->with('error', __('client/checkout.session.expired'));
        }

        $user = Auth::user();

        try {
            $coupon = Coupon::with(['packages', 'tlds'])->where('code', $request->coupon_code)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->where(function ($q) {
                    $q->whereNull('start_at')->orWhere('start_at', '<=', now());
                })
                ->first();

            if (!$coupon) {
                throw new \RuntimeException(__('client/checkout.coupon.invalid'));
            }

            if ($coupon->max_uses && $coupon->total_uses >= $coupon->max_uses) {
                throw new \RuntimeException(__('client/checkout.coupon.limit_reached'));
            }

            if ($user && $coupon->max_uses_per_user) {
                $userUsage = CouponUsage::where('coupon_id', $coupon->id)->where('user_id', $user->id)->count();
                if ($userUsage >= $coupon->max_uses_per_user) {
                    throw new \RuntimeException(__('client/checkout.coupon.user_limit_reached'));
                }
            }

            $allowedPackages = $coupon->packages->pluck('id')->toArray();
            $allowedTlds = $coupon->tlds->pluck('id')->toArray();
            $allowedCycles = $coupon->billing_cycles ?? [];

            $isCartEligible = false;
            $hasPackages = !empty($allowedPackages);
            $hasTlds = !empty($allowedTlds);

            foreach ($cartItems as $item) {
                $isDomain = isset($item['type']) && $item['type'] === \App\OrderItemType::Domain->value;

                if ($isDomain) {
                    if ($hasPackages && !$hasTlds) {
                        $itemMatch = false;
                    } else {
                        $itemMatch = !$hasTlds || in_array($item['tld_id'] ?? null, $allowedTlds);
                    }
                    
                    $hasDomainCycle = false;
                    foreach ($allowedCycles as $cycle) {
                        if (str_contains(strtolower($cycle), 'year')) {
                            $hasDomainCycle = true;
                            break;
                        }
                    }
                    
                    if (!empty($allowedCycles) && $hasDomainCycle) {
                        $cycleMatch = in_array($item['cycle_name'], $allowedCycles);
                    } else {
                        $cycleMatch = true;
                    }
                } else {
                    if ($hasTlds && !$hasPackages) {
                        $itemMatch = false;
                    } else {
                        $itemMatch = !$hasPackages || in_array($item['package_id'] ?? null, $allowedPackages);
                    }
                    $cycleMatch = empty($allowedCycles) || in_array($item['cycle_name'], $allowedCycles);
                }

                if ($itemMatch && $cycleMatch) {
                    $isCartEligible = true;
                    break;
                }
            }

            if (!$isCartEligible) {
                throw new \RuntimeException(__('client/checkout.coupon.cart_mismatch'));
            }

            Session::put('applied_coupon', [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'allowed_packages' => $allowedPackages,
                'allowed_tlds' => $allowedTlds,
                'allowed_cycles' => $allowedCycles,
            ]);

            return redirect()->back()->with('success', __('client/checkout.coupon.applied'));

        } catch (\Exception $e) {
            Session::forget('applied_coupon');
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the currently applied coupon from the cart session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove()
    {
        Session::forget('applied_coupon');
        return redirect()->back()->with('success', __('client/checkout.coupon.removed'));
    }
}
