<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\PhpExecutableFinder;

class UpdateController extends Controller
{
    /**
     * Applies permission-based middleware for accessing the update system.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:update.view')->only(['index', 'check']);

        // progress() and status() require execute permission as they expose internal update state
        $this->middleware('permission:update.execute')->only(['execute', 'progress', 'status']);
    }

    /**
     * Display the system update page.
     *
     * @param \App\Services\UpdateService $updateService
     * @return \Illuminate\View\View
     */
    public function index(UpdateService $updateService)
    {
        $release = $updateService->getLatestRelease();
        $currentVersion = $updateService->getCurrentVersion();
        $isUpdateAvailable = $updateService->isUpdateAvailable();
        $requirements = $updateService->checkRequirements();
        $allRequirementsMet = collect($requirements)->every(fn ($r) => $r['satisfied']);

        return view('admin::update.index', compact(
            'release',
            'currentVersion',
            'isUpdateAvailable',
            'requirements',
            'allRequirementsMet',
        ));
    }

    /**
     * Force a fresh check for updates, bypassing the cache.
     *
     * @param \App\Services\UpdateService $updateService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function check(UpdateService $updateService)
    {
        $updateService->getLatestRelease(fresh: true);

        return redirect()->route('admin.update')
            ->with('success', __('admin/update.check_complete'));
    }

    /**
     * Start the update process by spawning a background artisan command,
     * then redirect to the progress page.
     *
     * @param Request       $request
     * @param UpdateService $updateService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function execute(Request $request, UpdateService $updateService)
    {
        // Prevent duplicate execution if already running
        $status = UpdateService::readStatus();
        if ($status['state'] === 'running') {
            return redirect()->route('admin.update.progress');
        }

        // Validate requirements and update availability
        $requirements = $updateService->checkRequirements();
        $allRequirementsMet = collect($requirements)->every(fn ($r) => $r['satisfied']);

        if (!$allRequirementsMet) {
            return redirect()->route('admin.update')
                ->with('error', __('admin/update.requirements_not_met'));
        }

        if (!$updateService->isUpdateAvailable()) {
            return redirect()->route('admin.update')
                ->with('error', __('admin/update.no_update'));
        }

        $release = $updateService->getLatestRelease();

        Audit::system(Auth::id(), 'system.update.started', [
            'from_version' => $updateService->getCurrentVersion(),
            'to_version' => $release['tag_name'] ?? 'unknown',
        ]);

        // Spawn the artisan command as a truly detached background process
        $this->spawnUpdateProcess(dryRun: false);

        return redirect()->route('admin.update.progress');
    }

    /**
     * Display the update progress page with real-time log polling.
     *
     * @return \Illuminate\View\View
     */
    public function progress()
    {
        return view('admin::update.progress');
    }

    /**
     * Return the current update status and logs as JSON (for AJAX polling).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(UpdateService $updateService)
    {
        $status = UpdateService::readStatus();
        $logs = UpdateService::readLogs();

        // Auto-cleanup: remove progress/status files after 1 hour in a terminal state
        // so the progress page starts fresh on the next update cycle.
        if (in_array($status['state'], ['completed', 'failed', 'idle']) && isset($status['updated_at'])) {
            $finishedAt = \Carbon\Carbon::parse($status['updated_at']);
            if ($finishedAt->diffInHours(now()) >= 1) {
                $updateService->cleanupUpdateState();
            }
        }

        return response()->json([
            'status' => $status,
            'logs' => $logs,
        ]);
    }

    /**
     * Spawn the update artisan command as a truly detached background process
     * using proc_open, which properly detaches on both Windows and Unix.
     *
     * Unlike Symfony\Process::start(), proc_open with NUL/dev-null descriptors
     * ensures the child process is not terminated when the HTTP connection closes.
     *
     * @param bool $dryRun
     * @return void
     */
    private function spawnUpdateProcess(bool $dryRun): void
    {
        $phpBinary = (new PhpExecutableFinder())->find() ?: 'php';
        $artisan = base_path('artisan');

        $command = [$phpBinary, $artisan, 'billmora:update:execute'];

        if ($dryRun) {
            $command[] = '--dry-run';
        }

        $isWindows = PHP_OS_FAMILY === 'Windows';
        $null = $isWindows ? 'NUL' : '/dev/null';

        // Redirect all stdio to null so the process is fully detached
        $descriptors = [
            0 => ['file', $null, 'r'],   // stdin
            1 => ['file', $null, 'w'],   // stdout
            2 => ['file', $null, 'w'],   // stderr
        ];

        $commandString = implode(' ', array_map('escapeshellarg', $command));

        $proc = proc_open($commandString, $descriptors, $pipes, base_path());

        if (is_resource($proc)) {
            Log::channel('single')->info('[SystemUpdate] Detached background process spawned', [
                'command' => $commandString,
            ]);

            // Immediately release — the process continues independently
            proc_close($proc);
        } else {
            Log::channel('single')->error('[SystemUpdate] Failed to spawn background process', [
                'command' => $commandString,
            ]);
        }
    }
}
