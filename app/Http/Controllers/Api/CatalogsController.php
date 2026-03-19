<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CatalogResource;
use App\Models\Catalog;
use Illuminate\Http\Request;

class CatalogsController extends Controller
{
    /**
     * Display a paginated listing of catalogs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $catalogs = Catalog::query()
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return CatalogResource::collection($catalogs);
    }

    /**
     * Display the specified catalog.
     *
     * @param  \App\Models\Catalog  $catalog
     * @return \App\Http\Resources\CatalogResource
     */
    public function show(Catalog $catalog)
    {
        $catalog->load('packages');

        return new CatalogResource($catalog);
    }

    /**
     * Store a newly created catalog.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\CatalogResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $catalog = Catalog::create($validated);

        return (new CatalogResource($catalog))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified catalog.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Catalog  $catalog
     * @return \App\Http\Resources\CatalogResource
     */
    public function update(Request $request, Catalog $catalog)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ]);

        $catalog->update($validated);

        return new CatalogResource($catalog->fresh());
    }

    /**
     * Remove the specified catalog.
     *
     * @param  \App\Models\Catalog  $catalog
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Catalog $catalog)
    {
        $catalog->delete();

        return response()->json(['message' => 'Catalog deleted successfully.'], 200);
    }
}
