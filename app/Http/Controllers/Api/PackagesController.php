<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Illuminate\Http\Request;

class PackagesController extends Controller
{
    /**
     * Display a paginated listing of packages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $packages = Package::with('catalog')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return PackageResource::collection($packages);
    }

    /**
     * Display the specified package.
     *
     * @param  \App\Models\Package  $package
     * @return \App\Http\Resources\PackageResource
     */
    public function show(Package $package)
    {
        $package->load(['catalog', 'prices', 'variants.options.prices']);

        return new PackageResource($package);
    }

    /**
     * Store a newly created package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\PackageResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:packages,slug'],
            'description' => ['nullable', 'string'],
            'catalog_id' => ['required', 'exists:catalogs,id'],
            'status' => ['nullable', 'string', 'in:visible,hidden'],
            'icon' => ['nullable', 'string', 'max:255'],
            'stock' => ['nullable', 'integer', 'min:-1'],
            'per_user_limit' => ['nullable', 'integer', 'min:-1'],
            'allow_cancellation' => ['nullable', 'boolean'],
            'allow_quantity' => ['nullable', 'string', 'in:single,multiple'],
            'prorata_day' => ['nullable', 'integer', 'min:0', 'max:31'],
            'auto_provision' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'plugin_id' => ['nullable', 'exists:plugins,id'],
            'provisioning_config' => ['nullable', 'array'],
        ]);

        $package = Package::create($validated);

        return (new PackageResource($package->load('catalog')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Package  $package
     * @return \App\Http\Resources\PackageResource
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:packages,slug,' . $package->id],
            'description' => ['sometimes', 'nullable', 'string'],
            'catalog_id' => ['sometimes', 'exists:catalogs,id'],
            'status' => ['sometimes', 'string', 'in:visible,hidden'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:255'],
            'stock' => ['sometimes', 'nullable', 'integer', 'min:-1'],
            'per_user_limit' => ['sometimes', 'nullable', 'integer', 'min:-1'],
            'allow_cancellation' => ['sometimes', 'nullable', 'boolean'],
            'allow_quantity' => ['sometimes', 'nullable', 'string', 'in:single,multiple'],
            'prorata_day' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:31'],
            'auto_provision' => ['sometimes', 'nullable', 'boolean'],
            'sort_order' => ['sometimes', 'nullable', 'integer'],
            'plugin_id' => ['sometimes', 'nullable', 'exists:plugins,id'],
            'provisioning_config' => ['sometimes', 'nullable', 'array'],
        ]);

        $package->update($validated);

        return new PackageResource($package->fresh()->load('catalog'));
    }

    /**
     * Remove the specified package.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Package $package)
    {
        $package->delete();

        return response()->json(['message' => 'Package deleted successfully.'], 200);
    }
}
