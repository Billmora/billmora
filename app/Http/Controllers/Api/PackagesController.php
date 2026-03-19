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
            'description' => ['nullable', 'string'],
            'catalog_id' => ['required', 'exists:catalogs,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
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
            'description' => ['sometimes', 'nullable', 'string'],
            'catalog_id' => ['sometimes', 'exists:catalogs,id'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
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
