<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponsController extends Controller
{
    use AuditsSystem;
    /**
     * Applies permission-based middleware for accessing coupons.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:coupons.view')->only(['index']);
        $this->middleware('permission:coupons.create')->only(['create', 'store']);
        $this->middleware('permission:coupons.update')->only(['edit', 'update']);
        $this->middleware('permission:coupons.delete')->only(['destroy']);
    }

    /**
     * Display a listing of coupons.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $coupons = Coupon::select('id', 'code', 'start_at', 'expires_at', 'total_uses', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(Billmora::getGeneral('misc_admin_pagination'));
        
        return view('admin::coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new coupon.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $packageOptions = Package::query()
            ->select(['id', 'name', 'catalog_id'])
            ->with([
                'catalog:id,name',
            ])
            ->get()
            ->map(fn ($package) => [
                'value' => $package->id,
                'title' => $package->name,
                'subtitle' => $package->catalog->name,
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

    /**
     * Store a newly created coupon in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

        $this->recordCreate('coupon.create', $coupon->toArray());

        return redirect()->route('admin.coupons')->with('success',  __('common.create_success', ['attribute' => $coupon->code]));
    }

    /**
     * Show the form for editing the specified coupon.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\View\View
     */
    public function edit(Coupon $coupon)
    {
        $coupon->load('packages');
    
        $packageOptions = Package::query()
            ->select(['id', 'name', 'catalog_id'])
            ->with([
                'catalog:id,name',
            ])
            ->get()
            ->map(fn ($package) => [
                'value' => $package->id,
                'title' => $package->name,
                'subtitle' => $package->catalog->name,
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

    /**
     * Update the specified coupon in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Coupon $coupon)
    {
        
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

        $couponOld = $coupon->getOriginal();
        $packagesOld = $coupon->packages()->pluck('packages.id')->sort()->values()->all();

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

        $changes = $coupon->getChanges();

        $packagesNew = collect($validated['coupon_packages'] ?? [])
            ->sort()
            ->values()
            ->all();
            
        if ($packagesOld !== $packagesNew) {
            $changes['package_ids'] = $packagesNew;
            $couponOld['package_ids'] = $packagesOld;
        }

        $this->recordUpdate('coupon.update', $couponOld, $changes);

        return redirect()->route('admin.coupons')->with('success', __('common.update_success', ['attribute' => $coupon->code]));
    }

    /**
     * Remove the specified coupon from database.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Coupon $coupon)
    {

        $coupon->delete();

        $this->recordDelete('coupon.delete', [
            'id' => $coupon->id,
            'code' => $coupon->code,
        ]);

        return redirect()->route('admin.coupons')->with('success', __('common.delete_success', ['attribute' => $coupon->code]));
    }
}