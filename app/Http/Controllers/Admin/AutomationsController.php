<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditSystem;
use Billmora;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AutomationsController extends Controller
{
    /**
     * Applies permission-based middleware for accessing automations system.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:automations.view')->only(['index']);
    }

    /**
     * Display the automation dashboard with schedule timing and aggregated activity statistics.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $scheduledTime = Billmora::getAutomation('time_of_day');
        $now = now();
        
        try {
            $scheduleToday = Carbon::createFromFormat('H:i', $scheduledTime);
        } catch (\Exception $e) {
            $scheduleToday = now()->startOfDay();
        }

        if ($now->greaterThanOrEqualTo($scheduleToday)) {
            $lastRun = $scheduleToday;
            $nextRun = $scheduleToday->copy()->addDay();
        } else {
            $lastRun = $scheduleToday->copy()->subDay();
            $nextRun = $scheduleToday;
        }

        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $stats = [
            'invoices_generated_month' => AuditSystem::where('event', 'invoice.created')->where('properties->actor', 'cron')->where('created_at', '>=', $thisMonth)->count(),
            'reminders_sent_today' => AuditSystem::where('event', 'invoice.notice.sent')->where('created_at', '>=', $today)->count(),
            'reminders_sent_month' => AuditSystem::where('event', 'invoice.notice.sent')->where('created_at', '>=', $thisMonth)->count(),
            'services_suspended_month' => AuditSystem::where('event', 'service.provisioning.suspend')->where('properties->status', 'success')->where('created_at', '>=', $thisMonth)->count(),
            'services_terminated_month' => AuditSystem::where('event', 'service.provisioning.terminate')->where('properties->status', 'success')->where('created_at', '>=', $thisMonth)->count(),
            'cancellations_processed_month' => AuditSystem::where('event', 'service.cancellation.approved')->where('created_at', '>=', $thisMonth)->count(),
            'tickets_closed_month' => AuditSystem::where('event', 'ticket.close')->where('created_at', '>=', $thisMonth)->count(),

            'invoices_generated_today' => AuditSystem::where('event', 'invoice.created')->where('properties->actor', 'cron')->where('created_at', '>=', $today)->count(),
            'reminders_sent_today' => AuditSystem::where('event', 'invoice.notice.sent')->where('created_at', '>=', $today)->count(),
            'services_suspended_today' => AuditSystem::where('event', 'service.provisioning.suspend')->where('properties->status', 'success')->where('created_at', '>=', $today)->count(),
            'services_terminated_today' => AuditSystem::where('event', 'service.provisioning.terminate')->where('properties->status', 'success')->where('created_at', '>=', $today)->count(),
            'cancellations_processed_today' => AuditSystem::where('event', 'service.cancellation.approved')->where('created_at', '>=', $today)->count(),
            'tickets_closed_today' => AuditSystem::where('event', 'ticket.close')->where('created_at', '>=', $today)->count(),
        ];

        return view('admin::automations.index', compact('lastRun', 'nextRun', 'stats', 'scheduledTime'));
    }
}
