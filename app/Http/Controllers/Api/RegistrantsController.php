<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegistrantResource;
use App\Models\Registrant;
use Illuminate\Http\Request;

class RegistrantsController extends Controller
{
    /**
     * Display a paginated listing of registrants.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $registrants = Registrant::with(['user', 'tld'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->user_id, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($request->search, fn($q, $search) => $q->where('registrant_number', 'like', "%{$search}%")
                ->orWhere('domain', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return RegistrantResource::collection($registrants);
    }

    /**
     * Display the specified registrant.
     *
     * @param  \App\Models\Registrant  $registrant
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Registrant $registrant)
    {
        $registrant->load(['user', 'tld']);

        return new RegistrantResource($registrant);
    }
}
