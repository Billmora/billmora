<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    /**
     * Display a paginated listing of services.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $services = Service::with(['user', 'package'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->user_id, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($request->search, fn($q, $search) => $q->where('service_number', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return ServiceResource::collection($services);
    }

    /**
     * Display the specified service.
     *
     * @param  \App\Models\Service  $service
     * @return \App\Http\Resources\ServiceResource
     */
    public function show(Service $service)
    {
        $service->load(['user', 'package']);

        return new ServiceResource($service);
    }

    /**
     * Update the specified service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \App\Http\Resources\ServiceResource
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:active,suspended,terminated,cancelled,pending'],
            'name' => ['sometimes', 'string', 'max:255'],
            'next_due_date' => ['sometimes', 'date'],
        ]);

        $service->update($validated);

        return new ServiceResource($service->fresh()->load(['user', 'package']));
    }

    /**
     * Remove the specified service.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully.'], 200);
    }
}
