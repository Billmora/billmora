<?php

namespace App\Http\Controllers\Admin\Themes;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class ConfigController extends Controller
{
    /**
     * Applies permission-based middleware for accessing themes config.
     * * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:themes.update')->only(['index', 'update']);
    }

    /**
     * Show the custom configuration page provided by the theme developer.
     *
     * @param \App\Models\Theme $theme
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Theme $theme)
    {
        $basePath = resource_path("themes/{$theme->type}/{$theme->provider}");
        
        if (!File::exists("{$basePath}/config.blade.php")) {
            return back()->with('error', __('admin/themes.configure.not_provide'));
        }

        View::addNamespace('theme', $basePath);

        return view('theme::config', compact('theme'));
    }

    /**
     * Update the theme configuration (JSON data).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Theme $theme
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Theme $theme)
    {
        $configData = $request->except(['_token', '_method']);

        $currentConfig = $theme->config ?? [];
        $newConfig = array_merge($currentConfig, $configData);

        $theme->update([
            'config' => $newConfig
        ]);

        if ($theme->is_active) {
            Cache::forget("theme_config_{$theme->type}");
        }

        Audit::system(Auth::id(), 'theme.update.config', $theme->toArray());

        return back()->with('success', __('common.update_success', ['attribute' => $theme->name]));
    }
}
