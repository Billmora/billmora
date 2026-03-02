<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceCancellation;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CancellationController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing action services.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:services.cancellations.view')->only(['index', 'edit']);
        $this->middleware('permission:services.cancellations.approve')->only(['approve']);
        $this->middleware('permission:services.cancellations.reject')->only(['reject']);
        $this->middleware('permission:services.cancellations.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of all service cancellation requests.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ServiceCancellation::with(['service.package.catalog', 'user', 'reviewedBy']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('service', function ($serviceQuery) use ($search) {
                    $serviceQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $cancellations = $query->latest()->paginate(25);
            
        return view('admin::services.cancellations.index', compact('cancellations'));
    }

    /**
     * Display the edit form for the specified cancellation request.
     *
     * @param  \App\Models\ServiceCancellation  $cancellation
     * @return \Illuminate\View\View
     */
    public function edit(ServiceCancellation $cancellation)
    {
        $cancellation->loadMissing(['user', 'reviewedBy']);

        return view('admin::services.cancellations.edit', compact('cancellation'));
    }

    /**
     * Approve the specified pending cancellation request and update the service status accordingly.
     *
     * @param  \App\Models\ServiceCancellation  $cancellation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(ServiceCancellation $cancellation)
    {
        abort_if($cancellation->status !== 'pending', 403);

        $oldCancellation = $cancellation->getOriginal();

        $cancellation = DB::transaction(function () use ($cancellation) {
            $now = now();

            $cancellation->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => $now,
                'cancelled_at' => $cancellation->type === 'immediate'
                    ? $now
                    : $cancellation->service->next_due_date,
            ]);

            if ($cancellation->type === 'immediate') {
                $cancellation->service->update(['status' => 'cancelled']);
            }

            return $cancellation;
        });

        $this->recordUpdate('service.cancellation.approve', $oldCancellation, $cancellation->getChanges());

        return back()->with('success', __('admin/services.cancellation.approved'));
    }

    /**
     * Reject the specified pending cancellation request with a provided rejection note.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServiceCancellation  $cancellation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, ServiceCancellation $cancellation)
    {
        abort_if($cancellation->status !== 'pending', 403);

        $validated = $request->validate([
            'cancellation_rejection_note' => ['required', 'string', 'max:500'],
        ]);

        $oldCancellation = $cancellation->getOriginal();

        $cancellation->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_note' => $validated['cancellation_rejection_note'],
        ]);

        $this->recordUpdate('service.cancellation.reject', $oldCancellation, $cancellation->getChanges());

        return back()->with('success', __('admin/services.cancellation.rejected'));
    }

    /**
     * Delete the specified service cancellation request from the system.
     *
     * @param  \App\Models\ServiceCancellation  $cancellation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ServiceCancellation $cancellation)
    {
        $cancellation->delete();

        $this->recordDelete('service.cancellation.delete', $cancellation->toArray());

        return back()->with('success', __('common.delete_success', ['attribute' => $cancellation->id]));
    }
}
