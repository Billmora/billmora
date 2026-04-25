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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Tld $tld)
    {
        $tld->load(['plugin', 'prices']);

        return new TldResource($tld);
    }
}
