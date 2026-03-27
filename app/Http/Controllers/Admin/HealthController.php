<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillmoraService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;

class HealthController extends Controller
{
    /**
     * Applies permission-based middleware for accessing the health system.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:health.view');
    }

    /**
     * Display the application health dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $health = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'environment' => App::environment(),
            'debug' => config('app.debug'),
            'version' => [
                'current' => BillmoraService::VERSION,
                'latest' => $this->getLatestVersion(),
            ],
            'php_version' => PHP_VERSION,
            'laravel_version' => App::version(),
        ];

        return view('admin::health.index', compact('health'));
    }

    /**
     * Check database connection status.
     *
     * @return bool
     */
    protected function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache store status.
     *
     * @return bool
     */
    protected function checkCache(): bool
    {
        try {
            Cache::store()->put('health_check', true, 5);
            return Cache::store()->get('health_check') === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Fetch the latest version from GitHub API.
     *
     * @return string|null
     */
    protected function getLatestVersion(): ?string
    {
        return Cache::remember('billmora_latest_version', 86400, function () {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'Billmora-Health-Check'])
                    ->get('https://api.github.com/repos/billmora/billmora/releases/latest');

                if ($response->successful()) {
                    return $response->json('tag_name');
                }
            } catch (\Exception $e) {
                // Return null if API call fails
            }
            return null;
        });
    }
}
