<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantsController extends Controller
{

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
            ->paginate(25);

        return view('admin::variants.index', compact('variants'));
    }

    /**
     * Show the form for creating a new variant.
     *
     * @return \Illuminate\View\View
     */
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
            'variant_is_upgradable' => ['required', 'boolean'],
            'variant_packages' => ['required', 'array'],
            'variant_packages.*' => ['integer', Rule::exists('packages', 'id')],
        ]);

        $variant = Variant::create([
            'name' => $validated['variant_name'],
            'description' => $validated['variant_description'] ?? null,
            'type' => $validated['variant_type'],
            'code' => $validated['variant_code'],
            'status' => $validated['variant_status'],
            'is_upgradable' => $validated['variant_is_upgradable'],
        ]);

        $variant->packages()->sync($validated['variant_packages']);

        return redirect()->route('admin.variants')->with('success', __('common.create_success', ['attribute' => $variant->name]));
    }

    /**
     * Show the form for editing the specified variant.
     *
     * @param  int  $id  Variant ID
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function edit($id)
    {
        $variant = Variant::with([ 'packages:id,name,catalog_id'])->findOrFail($id);

        $packageOptions = Package::query()
            ->select(['id', 'name', 'catalog_id'])
            ->with([
                'catalog:id,name',
            ])
            ->get()
            ->map(fn ($package) => [
                'value' => $package->id,
                'title' => "{$package->catalog->name} - {$package->name}",
            ])
            ->values();

        return view('admin::variants.edit', compact('variant', 'packageOptions'));
    }

    /**
     * Update the specified variant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Variant ID
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $variant = Variant::findOrFail($id);

        $validated = $request->validate([
            'variant_name' => ['required', 'string', 'max:255'],
            'variant_description' => ['nullable', 'string'],
            'variant_type' => ['required', 'in:select,radio,slider,checkbox'],
            'variant_code' => ['required', 'string'],
            'variant_status' => ['required', 'in:visible,hidden'],
            'variant_is_upgradable' => ['required', 'boolean'],
            'variant_packages' => ['required', 'array'],
            'variant_packages.*' => ['integer', Rule::exists('packages', 'id')],
        ]);

        $variant->update([
            'name' => $validated['variant_name'],
            'description' => $validated['variant_description'] ?? null,
            'type' => $validated['variant_type'],
            'code' => $validated['variant_code'],
            'status' => $validated['variant_status'],
            'is_upgradable' => $validated['variant_is_upgradable'],
        ]);

        $variant->packages()->sync($validated['variant_packages']);

        return redirect()->route('admin.variants.edit', ['id' => $variant->id])->with('success', __('common.update_success', ['attribute' => $variant->name]));
    }

    /**
     * Remove the specified variant from storage.
     *
     * @param  int  $id  Variant ID
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function destroy($id)
    {
        $variant = Variant::findOrFail($id);

        $variant->delete();

        return redirect()->route('admin.variants')->with('success', __('common.delete_success', ['attribute' => $variant->name]));
    }
}
