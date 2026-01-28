<?php

namespace App\Http\Controllers\Client\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\PackagePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CouponController extends Controller
{
    /**
     * Validate and apply coupon code to current checkout session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function check(Request $request)
    {
        $request->validate([
            'coupon_code' => ['required', 'string'],
        ]);

        $checkoutData = Session::get('checkout_data');

        if (!$checkoutData) {
            return redirect()->route('client.checkout.review')
                ->with('error', 'Session expired. Please select a package again.');
        }

        $user = Auth::user();
        $packagePrice = PackagePrice::with('package')->findOrFail($checkoutData['price_id']);

        try {
            $coupon = $this->validateCoupon($request->coupon_code, $packagePrice->package, $user, $packagePrice->name);

            Session::put('applied_coupon', [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ]);

            return redirect()
                ->route('client.checkout.review')
                ->with('success', 'Coupon applied successfully!');

        } catch (\Exception $e) {
            Session::forget('applied_coupon');

            return redirect()
                ->route('client.checkout.review')
                ->with('error', $e->getMessage());
        }

    }

    /**
     * Remove applied coupon from checkout session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove()
    {
        $checkoutData = Session::get('checkout_data');

        if (!$checkoutData) {
            return redirect()->route('client.checkout.review')
                ->with('error', 'Session expired.');
        }

        Session::forget('applied_coupon');

        return redirect()
            ->route('client.checkout.review')
            ->with('success', 'Coupon removed successfully.');
    }

    /**
     * Validate coupon eligibility for package, billing cycle, and user.
     *
     * @param string $code
     * @param \App\Models\Package $package
     * @param \App\Models\User $user
     * @param string $billingCycleName
     * @return \App\Models\Coupon
     * @throws \RuntimeException
     */
    protected function validateCoupon(string $code, $package, $user, string $billingCycleName): Coupon
    {
        $coupon = Coupon::where('code', $code)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('start_at')
                ->orWhere('start_at', '<=', now());
            })
            ->first();

        if (!$coupon) {
            throw new \RuntimeException('Invalid or expired coupon code.');
        }

        if ($coupon->packages()->exists() && !$coupon->packages->contains($package->id)) {
            throw new \RuntimeException('This coupon is not applicable to the selected package.');
        }

        if (!empty($coupon->billing_cycles) && !in_array($billingCycleName, $coupon->billing_cycles)) {
            throw new \RuntimeException('This coupon is not applicable to the selected billing cycle.');
        }

        if ($coupon->max_uses && $coupon->total_uses >= $coupon->max_uses) {
            throw new \RuntimeException('This coupon has reached its usage limit.');
        }

        if ($coupon->max_uses_per_user) {
            $userUsage = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->count();

            if ($userUsage >= $coupon->max_uses_per_user) {
                throw new \RuntimeException('You have reached the usage limit for this coupon.');
            }
        }

        return $coupon;
    }
}
