<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VariantResource;
use App\Models\Variant;
use Illuminate\Http\Request;

class VariantsController extends Controller
{
    /**
     * Display a paginated listing of variants.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $variants = Variant::with('options.prices')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return VariantResource::collection($variants);
    }

    /**
     * Display the specified variant.
     *
     * @param  \App\Models\Variant  $variant
     * @return \App\Http\Resources\VariantResource
     */
    public function show(Variant $variant)
    {
        $variant->load('options.prices');

        return new VariantResource($variant);
    }

    /**
     * Store a newly created variant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\VariantResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'is_scalable' => ['nullable', 'boolean'],
        ]);

        $variant = Variant::create($validated);

        return (new VariantResource($variant->load('options.prices')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified variant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant
     * @return \App\Http\Resources\VariantResource
     */
    public function update(Request $request, Variant $variant)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'is_scalable' => ['sometimes', 'boolean'],
        ]);

        $variant->update($validated);

        return new VariantResource($variant->fresh()->load('options.prices'));
    }

    /**
     * Remove the specified variant.
     *
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Variant $variant)
    {
        $variant->delete();

        return response()->json(['message' => 'Variant deleted successfully.'], 200);
    }
}
