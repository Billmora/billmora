<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

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
        $this->middleware('permission:update.execute')->only(['execute']);
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
        if (!auth()->user()->can('update.execute')) {
            abort(403);
        }

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

        // Spawn the artisan command as a background process
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
        if (!auth()->user()->can('update.execute')) {
            abort(403);
        }

        return view('admin::update.progress');
    }

    /**
     * Return the current update status and logs as JSON (for AJAX polling).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        if (!auth()->user()->can('update.execute')) {
            abort(403);
        }

        return response()->json([
            'status' => UpdateService::readStatus(),
            'logs' => UpdateService::readLogs(),
        ]);
    }

    /**
     * Spawn the update artisan command as an independent background process.
     * Works on both Windows and Unix systems, independent of queue workers.
     *
     * @param bool     $dryRun
     * @param int|null $userId
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

        $process = new Process($command);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(null);
        $process->disableOutput();

        // Start the process without waiting for it to complete
        $process->start();

        // Detach immediately so the HTTP response is not blocked
        // The process continues running in the background
        Log::channel('single')->info('[SystemUpdate] Background process spawned', [
            'command' => implode(' ', $command),
            'pid' => $process->getPid(),
        ]);
    }
}
