<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Models\AuditSystem;
use App\Models\Service;
use App\Services\ProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TasksController extends Controller
{
    /**
     * Applies permission-based middleware for accessing tasks system.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:tasks.view')->only(['index']);
        $this->middleware('permission:tasks.retry')->only(['retry']);
        $this->middleware('permission:tasks.dismiss')->only(['dismiss']);
    }

    public function index(Request $request)
    {
        $query = AuditSystem::where('properties->status', 'failed');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                  ->orWhere('properties', 'like', "%{$search}%");
            });
        }

        $failedTasks = $query->orderByDesc('created_at')->paginate(25);

        $failedTasks->appends(['search' => $search]);

        return view('admin::tasks.index', compact('failedTasks'));
    }

    public function retry(Request $request, AuditSystem $task, ProvisioningService $provisioningService)
    {
        $properties = $task->properties ?? [];

        if (($properties['status'] ?? '') !== 'failed') {
            return back()->with('error', __('admin/tasks.not_failed_state'));
        }

        try {
            if (str_starts_with($task->event, 'service.provisioning.')) {
                return $this->retryProvisioningTask($task, $properties, $provisioningService);
            }

            // Maybe in the future :3
            // if (str_starts_with($task->event, 'email.')) { ... }

            return back()->with('error', __('admin/tasks.retry_not_implemented'));

        } catch (\Throwable $e) {
            Log::error("Failed to retry task ID {$task->id}: " . $e->getMessage());
            
            $properties['message'] = $e->getMessage();
            $task->update(['properties' => $properties]);

            return back()->with('error', __('admin/tasks.retry_failed', [
                'message' => $e->getMessage(),
            ]));
        }
    }

    /**
     * Membatalkan (Dismiss) task dari antrean tanpa mengeksekusinya.
     */
    public function dismiss(AuditSystem $task)
    {
        $properties = $task->properties ?? [];
        
        $properties['status'] = 'dismissed';
        $properties['dismissed_at'] = now()->toDateTimeString();
        $properties['dismissed_by'] = Auth::id();
        
        $task->update(['properties' => $properties]);

        return back()->with('success', __('admin/tasks.dismissed'));
    }

    /**
     * Logika internal untuk mengeksekusi ulang Provisioning
     */
    private function retryProvisioningTask(AuditSystem $task, array $properties, ProvisioningService $provisioningService)
    {
        $serviceId = $properties['service_id'] ?? null;
        if (!$serviceId) {
            return back()->with('error', __('admin/tasks.missing_service_id'));
        }

        $service = Service::with('provisioning')->find($serviceId);
        if (!$service) {
            return back()->with('error', __('admin/tasks.service_not_found'));
        }

        if (!$service->provisioning) {
            return back()->with('error', __('admin/tasks.no_provisioning_plugin'));
        }

        $action = str_replace('service.provisioning.', '', $task->event);

        [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);
        
        if (!method_exists($plugin, $action)) {
            return back()->with('error', __('admin/tasks.action_not_supported', [
                'action' => $action,
            ]));
        }

        $plugin->$action($service, $instanceConfig);

        switch ($action) {
            case 'create':
                $service->activate();
                break;
            case 'unsuspend':
                $service->unsuspend();
                break;
            case 'suspend':
                $service->suspend();
                break;
            case 'terminate':
                $service->terminate();
                break;
        }

        $properties['status'] = 'resolved';
        $properties['resolved_at'] = now()->toDateTimeString();
        $task->update(['properties' => $properties]);

        Audit::system(Auth::id(), $task->event, [
            'service_id' => $service->id,
            'status' => 'success',
            'note' => 'Successfully resolved via manual retry from System Tasks queue.',
        ]);

        return back()->with('success', __('admin/tasks.retry_resolved', [
            'action' => $action,
        ]));
    }
}
