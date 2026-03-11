<?php

namespace App\Http\Controllers\Admin\Audits;

use App\Http\Controllers\Controller;
use App\Models\AuditSystem;
use App\Models\User;
use Billmora;
use Illuminate\Http\Request;

class SystemController extends Controller
{

    /**
     * Applies permission-based middleware for accessing system audit logs.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:audit.system.logs.view')->only(['index', 'show']);
        $this->middleware('permission:audit.system.logs.export')->only(['export']);
        $this->middleware('permission:audit.system.logs.delete')->only(['clear']);
    }

    /**
     * Display a paginated list of system audit logs.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('searchLogsSystem');

        $logs = AuditSystem::select('id', 'event', 'user_id', 'created_at')
                                ->when($search, function ($query, $search) {
                                    $query->where(function ($q) use ($search) {
                                        $q->where('event', 'like', "%{$search}%");
                                    });
                                })
                                ->latest()
                                ->paginate(Billmora::getGeneral('misc_admin_pagination'))
                                ->withQueryString();

        return view('admin::audits.system.index', compact('logs'));
    }

    /**
     * Display details of a specific system audit log.
     *
     * @param int $id The ID of the system audit log.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($id)
    {
        $log = AuditSystem::findOrFail($id);
        $user = User::select('email')->find($log->user_id);

        return view('admin::audits.system.show', compact('log', 'user'));
    }

    /**
     * Export all system audit logs as a JSON file.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export()
    {
        $companyName = Billmora::getGeneral('company_name');
        $nowDate = now()->format('Ymd_His');

        $histories = AuditSystem::all();
        $filename = "{$companyName}_audit-system-logs-{$nowDate}.json";

        $json = $histories->toJson(JSON_PRETTY_PRINT);

        return response()->streamDownload(function() use ($json) {
            echo $json;
        }, $filename, ['Content-Type' => 'application/json']);
    }

    /**
     * Clear all system audit logs.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear()
    {
        AuditSystem::truncate();

        return redirect()->back()->with('success', __('common.clear_success', ['attribute' => __('admin/audits/system.title')]));
    }
}
