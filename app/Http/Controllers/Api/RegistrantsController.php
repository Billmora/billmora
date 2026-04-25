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
     * @return \App\Http\Resources\RegistrantResource
     */
    public function show(Registrant $registrant)
    {
        $registrant->load(['user', 'tld']);

        return new RegistrantResource($registrant);
    }

    /**
     * Update the specified registrant (nameservers, auto-renew, status).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Registrant  $registrant
     * @return \App\Http\Resources\RegistrantResource
     */
    public function update(Request $request, Registrant $registrant)
    {
        $validated = $request->validate([
            'status'      => ['sometimes', 'string', 'in:pending,pending_transfer,active,suspended,expired,cancelled'],
            'auto_renew'  => ['sometimes', 'boolean'],
            'nameservers' => ['sometimes', 'array', 'min:1', 'max:5'],
            'nameservers.*' => ['nullable', 'string', 'max:255'],
        ]);

        $registrant->update($validated);

        return new RegistrantResource($registrant->fresh()->load(['user', 'tld']));
    }

    /**
     * Remove the specified registrant.
     *
     * @param  \App\Models\Registrant  $registrant
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Registrant $registrant)
    {
        $registrant->delete();

        return response()->json(['message' => 'Registrant deleted successfully.'], 200);
    }
}