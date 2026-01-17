<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Package;
use App\Models\PackagePrice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponsController extends Controller
{
    public function index()
    {
        $coupons = Coupon::select('id', 'code', 'start_at', 'expires_at', 'total_uses', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(25);
        
        return view('admin::coupons.index', compact('coupons'));
    }

    public function create()
    {
        $packageOptions = Package::with('catalog')
            ->get()
            ->map(fn ($package) => [
                'value' => $package->id,
                'title' => "{$package->catalog->name} - {$package->name}",
            ])
            ->values()
            ->toArray();
        
        $billingCycleOptions = PackagePrice::select('name')
            ->distinct()
            ->orderBy('name')
            ->get()
            ->map(fn ($price) => [
                'value' => $price->name,
                'title' => $price->name,
            ])
            ->values()
            ->toArray();
        
        return view('admin::coupons.create', compact('packageOptions', 'billingCycleOptions'));
    }

    public function store(Request $request)
    {
        $availableBillingCycles = PackagePrice::select('name')
            ->distinct()
            ->pluck('name')
            ->toArray();

        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:255', 'regex:/^[A-Z0-9]+$/', Rule::unique('coupons', 'code')],
            'coupon_type' => ['required', Rule::in(['percentage', 'fixed_amount'])],
            'coupon_value' => ['required', 'numeric', 'min:0'],
            'coupon_billing_cycles' => ['nullable', 'array'],
            'coupon_billing_cycles.*' => ['string', Rule::in($availableBillingCycles)],
            'coupon_max_uses' => ['nullable', 'integer', 'min:1'],
            'coupon_max_uses_per_user' => ['nullable', 'integer', 'min:1'],
            'coupon_start_date' => ['nullable', 'date'],
            'coupon_expires_date' => ['nullable', 'date', 'after_or_equal:coupon_start_date'],
            'coupon_packages' => ['nullable', 'array'],
            'coupon_packages.*' => [Rule::exists('packages', 'id')],
        ]);

        $coupon = Coupon::create([
            'code' => $validated['coupon_code'],
            'type' => $validated['coupon_type'],
            'value' => $validated['coupon_value'],
            'billing_cycles' => !empty($validated['coupon_billing_cycles']) ? $validated['coupon_billing_cycles'] : null,
            'max_uses' => $validated['coupon_max_uses'] ?? null,
            'max_uses_per_user' => $validated['coupon_max_uses_per_user'] ?? null,
            'start_at' => $validated['coupon_start_date'] ?? null,
            'expires_at' => $validated['coupon_expires_date'] ?? null,
        ]);

        if (!empty($validated['coupon_packages'])) {
            $coupon->packages()->attach($validated['coupon_packages']);
        }

        return redirect()->route('admin.coupons')->with('success',  __('common.create_success', ['attribute' => $coupon->code]));
    }

    public function edit($id)
    {
        $coupon = Coupon::with('packages')->findOrFail($id);
    
        $packageOptions = Package::with('catalog')
            ->get()
            ->map(fn ($package) => [
                'value' => $package->id,
                'title' => "{$package->catalog->name} - {$package->name}",
            ])
            ->values()
            ->toArray();
        
        $billingCycleOptions = PackagePrice::select('name')
            ->distinct()
            ->orderBy('name')
            ->get()
            ->map(fn ($price) => [
                'value' => $price->name,
                'title' => $price->name,
            ])
            ->values()
            ->toArray();
        
        return view('admin::coupons.edit', compact('coupon', 'packageOptions', 'billingCycleOptions'));
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        
        $availableBillingCycles = PackagePrice::select('name')
            ->distinct()
            ->pluck('name')
            ->toArray();

        $validated = $request->validate([
           'coupon_code' => ['required', 'string', 'max:255', 'regex:/^[A-Z0-9]+$/', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'coupon_type' => ['required', Rule::in(['percentage', 'fixed_amount'])],
            'coupon_value' => ['required', 'numeric', 'min:0'],
            'coupon_billing_cycles' => ['nullable', 'array'],
            'coupon_billing_cycles.*' => ['string', Rule::in($availableBillingCycles)],
            'coupon_max_uses' => ['nullable', 'integer', 'min:1'],
            'coupon_max_uses_per_user' => ['nullable', 'integer', 'min:1'],
            'coupon_start_date' => ['nullable', 'date'],
            'coupon_expires_date' => ['nullable', 'date', 'after_or_equal:coupon_start_date'],
            'coupon_packages' => ['nullable', 'array'],
            'coupon_packages.*' => [Rule::exists('packages', 'id')],
        ]);

        $coupon->update([
            'code' => $validated['coupon_code'],
            'type' => $validated['coupon_type'],
            'value' => $validated['coupon_value'],
            'billing_cycles' => !empty($validated['coupon_billing_cycles']) ? $validated['coupon_billing_cycles'] : null,
            'max_uses' => $validated['coupon_max_uses'] ?? null,
            'max_uses_per_user' => $validated['coupon_max_uses_per_user'] ?? null,
            'start_at' => $validated['coupon_start_date'] ?? null,
            'expires_at' => $validated['coupon_expires_date'] ?? null,
        ]);

        if (isset($validated['coupon_packages'])) {
            $coupon->packages()->sync($validated['coupon_packages']);
        } else {
            $coupon->packages()->detach();
        }

        return redirect()->route('admin.coupons')->with('success', __('common.update_success', ['attribute' => $coupon->code]));
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);

        $coupon->delete();

        return redirect()->route('admin.coupons')->with('success', __('common.delete_success', ['attribute' => $coupon->code]));
    }
}