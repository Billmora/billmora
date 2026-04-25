<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Currency;
use App\Models\Package;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PackagesController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing packages product.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:packages.view')->only(['index']);
        $this->middleware('permission:packages.create')->only(['create', 'store']);
        $this->middleware('permission:packages.update')->only(['edit', 'update']);
        $this->middleware('permission:packages.delete')->only(['destroy']);
    }

    /**
     * Display a listing of packages with optional search filter.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing optional 'search' query.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Package::select('id', 'catalog_id', 'name', 'slug', 'status', 'icon', 'created_at')
            ->with(['catalog:id,name,slug']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhereHas('catalog', function ($catalogQuery) use ($search) {
                    $catalogQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                });
            });
        }

        $packages = $query->get();
        $groupedPackages = $packages->groupBy(fn ($package) => $package->catalog ? $package->catalog->name : 'Uncategorized');

        return view('admin::packages.index', compact('groupedPackages', 'packages'));
    }

    /**
     * Show the form for creating a new package.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $catalogs = Catalog::select('id', 'name', 'slug')->get();

        return view('admin::packages.create', compact('catalogs'));
    }

    /**
     * Store a newly created package in database.
     *
     * @param \Illuminate\Http\Request $request The request containing package fields and optional icon file.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'catalog_id' => ['required', Rule::exists('catalogs', 'id')],
            'package_name' => ['required', 'string', 'max:255'],
            'package_slug' => [
                'required',
                'string',
                'max:255', 
                Rule::unique('packages', 'slug')->where('catalog_id', $request->catalog_id)
            ],
            'package_description' => ['required', 'string'],
            'package_icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'package_stock' => ['required', 'integer', 'min:-1'],
            'package_per_user_limit' => ['required', 'integer', 'min:-1'],
            'package_allow_cancellation' => ['required', 'boolean'],
            'package_allow_quantity' => ['required', Rule::in(['single', 'multiple'])],
            'package_status' => ['required', 'in:visible,hidden'],
        ]);

        if ($request->package_icon) {
            $iconPath = $request->file('package_icon')->store('packages', 'public');
        }

        $package = Package::create([
            'catalog_id' => $validated['catalog_id'],
            'name' => $validated['package_name'],
            'slug' => $validated['package_slug'],
            'description' => $validated['package_description'],
            'icon' => $iconPath ?? null,
            'stock' => $validated['package_stock'],
            'per_user_limit' => $validated['package_per_user_limit'],
            'allow_cancellation' => $validated['package_allow_cancellation'],
            'allow_quantity' => $validated['package_allow_quantity'],
            'status' => $validated['package_status'],
        ]);

        $currencies = Currency::select('code')->get();

        $rates = [];

        foreach ($currencies as $currency) {
            $rates[$currency->code] = [
                'currency' => $currency->code,
                'price' => null,
                'setup_fee' => null,
                'enabled' => false,
            ];
        }

        $package->prices()->create([
            'name' => 'Forever',
            'type' => 'free',
            'time_interval' => null,
            'billing_period' => null,
            'rates' => $rates,
        ]);

        $this->recordCreate('package.create', $package->toArray());

        return redirect()->route('admin.packages.edit', ['package' => $package->id])->with('success', __('common.create_success', ['attribute' => $package->name]));
    }

    /**
     * Show the form for editing the specified package.
     *
     * @param \App\Models\Package $package The package identifier.
     * @return \Illuminate\View\View
     */
    public function edit(Package $package)
    {
        $catalogs = Catalog::select('id', 'name', 'slug')->get();

        return view('admin::packages.edit', compact('package', 'catalogs'));
    }

    /**
     * Update the specified package in database.
     *
     * @param \Illuminate\Http\Request $request The request containing updated fields and optional icon file.
     * @param \App\Models\Package $package The package identifier.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Package $package)
    {

        $validated = $request->validate([
            'catalog_id' => ['required', Rule::exists('catalogs', 'id')],
            'package_name' => ['required', 'string', 'max:255'],
            'package_slug' => [
                'required',
                'string',
                'max:255', 
                Rule::unique('packages', 'slug')->where('catalog_id', $request->catalog_id)->ignore($package->id)
            ],
            'package_description' => ['required', 'string'],
            'package_icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'package_stock' => ['required', 'integer', 'min:-1'],
            'package_per_user_limit' => ['required', 'integer', 'min:-1'],
            'package_allow_cancellation' => ['required', 'boolean'],
            'package_allow_quantity' => ['required', Rule::in(['single', 'multiple'])],
            'package_status' => ['required', 'in:visible,hidden'],
        ]);

        $icon = $package->icon;

        if ($request->input('remove_package_icon')) {
            if ($package->icon) {
                Storage::disk('public')->delete($package->icon);
            }
            $icon = null;
        }

        if ($request->package_icon) {
            $icon = $request->file('package_icon')->store('packages', 'public');
        }

        $oldPackage = $package->getOriginal();

        $package->update([
            'catalog_id' => $validated['catalog_id'],
            'name' => $validated['package_name'],
            'slug' => $validated['package_slug'],
            'description' => $validated['package_description'],
            'icon' => $icon,
            'stock' => $validated['package_stock'],
            'per_user_limit' => $validated['package_per_user_limit'],
            'allow_cancellation' => $validated['package_allow_cancellation'],
            'allow_quantity' => $validated['package_allow_quantity'],
            'status' => $validated['package_status'],
        ]);

        $this->recordUpdate('package.update', $oldPackage, $package->getChanges());

        return redirect()->route('admin.packages.edit', ['package' => $package->id])->with('success', __('common.update_success', ['attribute' => $package->name]));
    }

    /**
     * Remove the specified package from database.
     *
     * @param \App\Models\Package $package The package identifier.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Package $package)
    {
        if ($package->services()->where('status', 'active')->exists()) {
            return redirect()->route('admin.packages')->with('error', __('admin/packages.delete.has_services'));
        }

        $package->delete();
        
        $this->recordDelete('package.delete', $package->toArray());

        return redirect()->route('admin.packages')->with('success', __('common.delete_success', ['attribute' => $package->name]));
    }
}
