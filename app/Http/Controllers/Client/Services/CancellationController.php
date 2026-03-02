<?php

namespace App\Http\Controllers\Client\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCancellation;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CancellationController extends Controller
{
    use AuditsSystem;

    /**
     * Display the cancellation request form for the specified client service.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\View\View
     */
    public function create(Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);
        abort_if(!$service->package->allow_cancellation, 403);
        abort_if(!in_array($service->status, ['active', 'suspended']), 403);
        abort_if(
            $service->cancellations()->where('status', 'pending')->exists(),
            403
        );

        return view('client::services.workspaces.cancellation', compact('service'));
    }

    /**
     * Validate and store a new cancellation request for the specified client service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);
        abort_if(!$service->package->allow_cancellation, 403);
        abort_if(!in_array($service->status, ['active', 'suspended']), 403);
        abort_if(
            $service->cancellations()->where('status', 'pending')->exists(),
            403
        );

        $validated = $request->validate([
            'cancellation_type' => ['required', Rule::in('immediate', 'end_of_period')],
            'cancellation_reason' => ['required', 'string', 'max:1000'],
        ]);

        $cancellation = $service->cancellations()->create([
            'user_id' => Auth::id(),
            'type' => $validated['cancellation_type'],
            'reason' => $validated['cancellation_reason'],
            'status' => 'pending',
        ]);

        $this->recordCreate('service.cancellation.request', $cancellation->toArray());

        return redirect()
            ->route('client.services.show', $service->id)
            ->with('success', __('client/services.cancellation.requested'));
    }
}
