<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Execute the application update process with streamed real-time output.
     *
     * @param \App\Services\UpdateService $updateService
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function execute(UpdateService $updateService)
    {
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

        return new StreamedResponse(function () use ($updateService, $release) {
            echo $this->streamHead($release);
            if (ob_get_level()) { ob_end_flush(); }
            flush();

            $result = $updateService->executeUpdate(function ($entry) {
                $cls = 'log-' . ($entry['status'] ?? 'running');
                $icon = match ($entry['status']) {
                    'success' => '✅', 'error' => '❌', 'warning' => '⚠️', default => '⏳',
                };
                echo "<div class=\"log-entry\"><span class=\"log-time\">[{$entry['time']}]</span><span class=\"log-msg {$cls}\">{$icon} {$entry['message']}</span></div>";
                flush();
            });

            echo '</div>';

            if ($result['success']) {
                Audit::system(Auth::id(), 'system.update.completed', ['version' => $result['version']]);
                echo '<div class="result result-success">✅ ' . __('admin/update.update_success', ['version' => $result['version']]) . '</div>';
            } else {
                Audit::system(Auth::id(), 'system.update.failed', ['logs' => array_slice($result['logs'], -5)]);
                echo '<div class="result result-error">❌ ' . __('admin/update.update_failed') . '</div>';
            }

            echo '<a href="' . route('admin.update') . '" class="back-link">← ' . __('admin/update.back_to_update') . '</a>';
            echo '</div></div><script>document.getElementById("log-area").scrollTop=document.getElementById("log-area").scrollHeight;</script></body></html>';
        }, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Generate the HTML head and opening markup for the streamed update page.
     *
     * @param array|null $release
     * @return string
     */
    private function streamHead(?array $release): string
    {
        $title = __('admin/update.updating_title');
        $subtitle = __('admin/update.updating_subtitle', ['version' => $release['tag_name'] ?? '']);
        $css = <<<'CSS'
*{box-sizing:border-box}
body{font-family:"Inter","Segoe UI",system-ui,sans-serif;background:#f8fafc;color:#334155;margin:0;padding:16px}
.container{max-width:700px;margin:0 auto}
.card{background:#fff;border:2px solid #e2e8f0;border-radius:16px;padding:20px}
h1{font-size:20px;font-weight:700;margin:0 0 6px}
.subtitle{color:#64748b;font-size:13px;margin:0 0 20px;word-break:break-word}
.log-area{background:#0f172a;color:#e2e8f0;border-radius:12px;padding:14px;font-family:"JetBrains Mono","Fira Code","Courier New",monospace;font-size:12px;line-height:1.8;max-height:400px;overflow-y:auto;overflow-x:hidden}
.log-entry{display:flex;gap:8px;align-items:flex-start;flex-wrap:wrap}
.log-time{color:#64748b;flex-shrink:0;font-size:11px}
.log-msg{word-break:break-word;min-width:0}
.log-success{color:#4ade80}.log-error{color:#f87171}.log-warning{color:#fbbf24}.log-running{color:#60a5fa}
.result{margin-top:20px;padding:14px 16px;border-radius:12px;font-weight:600;font-size:14px;text-align:center;word-break:break-word}
.result-success{background:#f0fdf4;color:#16a34a;border:2px solid #bbf7d0}
.result-error{background:#fef2f2;color:#dc2626;border:2px solid #fecaca}
.back-link{display:inline-flex;align-items:center;gap:6px;margin-top:16px;color:#6366f1;text-decoration:none;font-weight:600;font-size:14px}
.back-link:hover{text-decoration:underline}
@media(min-width:640px){
body{padding:40px 20px}
.card{padding:32px}
h1{font-size:24px;margin-bottom:8px}
.subtitle{font-size:14px;margin-bottom:24px}
.log-area{padding:20px;font-size:13px;max-height:500px}
.log-entry{flex-wrap:nowrap}
.log-time{font-size:13px}
.result{padding:16px 20px;font-size:15px}
}
CSS;

        return <<<HTML
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title}</title><style>{$css}</style></head><body>
<div class="container"><div class="card">
<h1>🔄 {$title}</h1><p class="subtitle">{$subtitle}</p>
<div class="log-area" id="log-area">
HTML;
    }
}
