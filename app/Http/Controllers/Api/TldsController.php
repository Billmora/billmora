<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TldResource;
use App\Models\Tld;
use Illuminate\Http\Request;

class TldsController extends Controller
{
    /**
     * Display a paginated listing of TLDs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $tlds = Tld::with(['plugin'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, fn($q, $search) => $q->where('tld', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return TldResource::collection($tlds);
    }

    /**
     * Display the specified TLD.
     *
     * @param  \App\Models\Tld  $tld
     * @return \App\Http\Resources\TldResource
     */
    public function show(Tld $tld)
    {
        $tld->load(['plugin', 'prices']);

        return new TldResource($tld);
    }

    /**
     * Store a newly created TLD.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\TldResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tld'           => ['required', 'string', 'max:20', 'unique:tlds,tld'],
            'status'        => ['required', 'string', 'in:active,inactive'],
            'plugin_id'     => ['nullable', 'integer', 'exists:plugins,id'],
            'min_years'     => ['required', 'integer', 'min:1'],
            'max_years'     => ['required', 'integer', 'min:1'],
            'whois_privacy' => ['boolean'],
            'sort_order'    => ['nullable', 'integer'],
        ]);

        $tld = Tld::create($validated);

        return (new TldResource($tld))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified TLD.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tld  $tld
     * @return \App\Http\Resources\TldResource
     */
    public function update(Request $request, Tld $tld)
    {
        $validated = $request->validate([
            'tld'           => ['sometimes', 'string', 'max:20', 'unique:tlds,tld,' . $tld->id],
            'status'        => ['sometimes', 'string', 'in:active,inactive'],
            'plugin_id'     => ['nullable', 'integer', 'exists:plugins,id'],
            'min_years'     => ['sometimes', 'integer', 'min:1'],
            'max_years'     => ['sometimes', 'integer', 'min:1'],
            'whois_privacy' => ['sometimes', 'boolean'],
            'sort_order'    => ['nullable', 'integer'],
        ]);

        $tld->update($validated);

        return new TldResource($tld->fresh()->load(['plugin', 'prices']));
    }

    /**
     * Remove the specified TLD.
     *
     * @param  \App\Models\Tld  $tld
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Tld $tld)
    {
        $tld->delete();

        return response()->json(['message' => 'TLD deleted successfully.'], 200);
    }
}