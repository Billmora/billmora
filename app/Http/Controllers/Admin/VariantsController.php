<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Variant;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantsController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing variants product.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:variants.view')->only(['index']);
        $this->middleware('permission:variants.create')->only(['create', 'store']);
        $this->middleware('permission:variants.update')->only(['edit', 'update']);
        $this->middleware('permission:variants.delete')->only(['destroy']);
    }

    /**
     * Display a listing of variants with optional search filter.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $variants = Variant::query()
            ->select(['id', 'name', 'description', 'status', 'created_at'])
            ->with([
                'packages:id,name,catalog_id',
                'packages.catalog:id,name',
            ])
            ->paginate(Billmora::getGeneral('misc_admin_pagination'));

        return view('admin::variants.index', compact('variants'));
    }

    /**
     * Show the form for creating a new variant.
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

        return view('admin::variants.create', compact('packageOptions'));
    }
    
    /**
     * Store a newly created variant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'variant_name' => ['required', 'string', 'max:255'],
            'variant_description' => ['nullable', 'string'],
            'variant_type' => ['required', 'in:select,radio,slider,checkbox'],
            'variant_code' => ['required', 'string'],
            'variant_status' => ['required', 'in:visible,hidden'],
            'variant_is_scalable' => ['required', 'boolean'],
            'variant_packages' => ['required', 'array'],
            'variant_packages.*' => ['integer', Rule::exists('packages', 'id')],
        ]);

        $variant = Variant::create([
            'name' => $validated['variant_name'],
            'description' => $validated['variant_description'] ?? null,
            'type' => $validated['variant_type'],
            'code' => $validated['variant_code'],
            'status' => $validated['variant_status'],
            'is_scalable' => $validated['variant_is_scalable'],
        ]);

        $variant->packages()->sync($validated['variant_packages']);

        $this->recordCreate('variant.create', $variant->toArray());

        return redirect()->route('admin.variants.edit', ['variant' => $variant->id])->with('success', __('common.create_success', ['attribute' => $variant->name]));
    }

    /**
     * Show the form for editing the specified variant.
     *
     * @param  \App\Models\Variant  $variant  Variant ID
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function edit(Variant $variant)
    {
        $variant->load(['packages:id,name,catalog_id']);

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

        return view('admin::variants.edit', compact('variant', 'packageOptions'));
    }

    /**
     * Update the specified variant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant  Variant ID
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Variant $variant)
    {

        $validated = $request->validate([
            'variant_name' => ['required', 'string', 'max:255'],
            'variant_description' => ['nullable', 'string'],
            'variant_type' => ['required', 'in:select,radio,slider,checkbox'],
            'variant_code' => ['required', 'string'],
            'variant_status' => ['required', 'in:visible,hidden'],
            'variant_is_scalable' => ['required', 'boolean'],
            'variant_packages' => ['required', 'array'],
            'variant_packages.*' => ['integer', Rule::exists('packages', 'id')],
        ]);

        $variantOld = $variant->getOriginal();
        $packagesOld = $variant->packages()->pluck('packages.id')->sort()->values()->all();

        $variant->update([
            'name' => $validated['variant_name'],
            'description' => $validated['variant_description'] ?? null,
            'type' => $validated['variant_type'],
            'code' => $validated['variant_code'],
            'status' => $validated['variant_status'],
            'is_scalable' => $validated['variant_is_scalable'],
        ]);

        $variant->packages()->sync($validated['variant_packages']);

        $changes = $variant->getChanges();

        $packagesNew = collect($validated['variant_packages'])->sort()->values()->all();
        if ($packagesOld !== $packagesNew) {
            $changes['package_ids'] = $packagesNew;
            $variantOld['package_ids'] = $packagesOld;
        }

        $this->recordUpdate('variant.update', $variantOld, $changes);

        return redirect()->route('admin.variants.edit', ['variant' => $variant->id])->with('success', __('common.update_success', ['attribute' => $variant->name]));
    }

    /**
     * Remove the specified variant from storage.
     *
     * @param  \App\Models\Variant  $variant  Variant ID
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function destroy(Variant $variant)
    {

        $variant->delete();

        $this->recordDelete('variant.delete', [
            'id' => $variant->id,
            'name' => $variant->name,
        ]);

        return redirect()->route('admin.variants')->with('success', __('common.delete_success', ['attribute' => $variant->name]));
    }
}
