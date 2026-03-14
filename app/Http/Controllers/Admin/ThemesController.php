<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ZipArchive;

class ThemesController extends Controller
{
    /**
     * Applies permission-based middleware for accessing themes.
     * * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:themes.view')->only(['index']);
        $this->middleware('permission:themes.install')->only(['install']);
        $this->middleware('permission:themes.update')->only(['update']);
        $this->middleware('permission:themes.uninstall')->only(['uninstall']);
    }

    /**
     * Display a list of all installed themes from the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Theme::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
        }

        $themes = $query->orderBy('type')->get();

        return view('admin::themes.index', compact('themes'));
    }

    /**
     * Install a new theme from an uploaded ZIP archive.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function install(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme_file' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            ['manifest' => $manifest, 'source_dir' => $sourceDir, 'tmp_path' => $tmpPath] = $this->extractAndValidateZip($request->file('theme_file'));

            $type = strtolower($manifest['type']);
            $provider = Str::slug($manifest['provider']);
            $name = $manifest['name'];

            if (Theme::where('type', $type)->where('provider', $provider)->exists()) {
                File::deleteDirectory($tmpPath);
                return back()->with('error', __('admin/themes.install.already_exists', [
                    'provider' => $provider, 
                ]));
            }

            $resourcePath = resource_path("themes/{$type}/{$provider}");
            $publicPath = public_path("themes/{$type}/{$provider}");

            File::ensureDirectoryExists($resourcePath);
            
            if (File::exists($sourceDir . '/views')) {
                File::copyDirectory($sourceDir . '/views', $resourcePath . '/views');
            }
            File::copy($sourceDir . '/theme.json', $resourcePath . '/theme.json');
            
            if (File::exists($sourceDir . '/config.blade.php')) {
                File::copy($sourceDir . '/config.blade.php', $resourcePath . '/config.blade.php');
            }

            if (File::exists($sourceDir . '/assets')) {
                File::ensureDirectoryExists($publicPath);
                File::copyDirectory($sourceDir . '/assets', $publicPath);
            }

            File::deleteDirectory($tmpPath);

            Theme::create([
                'provider' => $provider,
                'name' => $name,
                'type' => $type,
                'is_active' => false,
                'is_core' => false,
                'config' => [],
            ]);

            Audit::system(Auth::id(), 'theme.install', $manifest);

            return back()->with('success', __('admin/themes.install.success', [
                'name' => $name,
            ]));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an existing installed theme from an uploaded ZIP archive.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Theme $theme
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Theme $theme)
    {
        $validator = Validator::make($request->all(), [
            'theme_file' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            ['manifest' => $manifest, 'source_dir' => $sourceDir, 'tmp_path' => $tmpPath] = $this->extractAndValidateZip($request->file('theme_file'));

            $uploadedType = strtolower($manifest['type']);
            $uploadedProvider = Str::slug($manifest['provider']);

            if ($uploadedType !== $theme->type || $uploadedProvider !== $theme->provider) {
                File::deleteDirectory($tmpPath);
                return back()->with('error', __('admin/themes.update.mismatch', [
                ]));
            }

            $resourcePath = resource_path("themes/{$theme->type}/{$theme->provider}");
            $publicPath = public_path("themes/{$theme->type}/{$theme->provider}");

            $theme->update(['name' => $manifest['name']]);

            File::deleteDirectory($resourcePath);
            File::ensureDirectoryExists($resourcePath);
            
            if (File::exists($sourceDir . '/views')) {
                File::copyDirectory($sourceDir . '/views', $resourcePath . '/views');
            }
            File::copy($sourceDir . '/theme.json', $resourcePath . '/theme.json');
            
            if (File::exists($sourceDir . '/config.blade.php')) {
                File::copy($sourceDir . '/config.blade.php', $resourcePath . '/config.blade.php');
            }

            File::deleteDirectory($publicPath);
            if (File::exists($sourceDir . '/assets')) {
                File::ensureDirectoryExists($publicPath);
                File::copyDirectory($sourceDir . '/assets', $publicPath);
            }

            File::deleteDirectory($tmpPath);

            Audit::system(Auth::id(), 'theme.update', $manifest);

            return back()->with('success', __('admin/themes.update.success', [
                'name' => $manifest['name'],
            ]));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Uninstall a theme by removing its directory and database record.
     *
     * @param \App\Models\Theme $theme
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uninstall(Theme $theme)
    {
        if ($theme->is_core) {
            return back()->with('error', __('admin/themes.uninstall.core_protected'));
        }

        if ($theme->is_active) {
            return back()->with('error', __('admin/themes.uninstall.active_protected'));
        }

        $resourcePath = resource_path("themes/{$theme->type}/{$theme->provider}");
        $publicPath = public_path("themes/{$theme->type}/{$theme->provider}");

        if (File::exists($resourcePath)) {
            File::deleteDirectory($resourcePath);
        }

        if (File::exists($publicPath)) {
            File::deleteDirectory($publicPath);
        }

        $theme->delete();

        Audit::system(Auth::id(), 'theme.uninstall', $theme->toArray());

        return back()->with('success', __('admin/themes.uninstall.success', [
            'name' => $theme->name,
        ]));
    }

    /**
     * Extract and validate a theme ZIP archive, returning the manifest and relevant paths.
     *
     * @param \Illuminate\Http\UploadedFile $zipFile
     * @return array{manifest: array, source_dir: string, tmp_path: string}
     *
     * @throws \Exception If the ZIP is corrupted, theme.json is missing, or the manifest is invalid.
     */
    private function extractAndValidateZip($zipFile): array
    {
        $zip = new ZipArchive;
        $tmpPath = storage_path('app/tmp/themes_' . uniqid());

        if ($zip->open($zipFile->path()) === true) {
            File::ensureDirectoryExists($tmpPath);
            $zip->extractTo($tmpPath);
            $zip->close();
        } else {
            throw new \Exception(__('admin/themes.extraction.corrupted_zip'));
        }

        $jsonPath = $tmpPath . '/theme.json';
        $sourceDir = $tmpPath;

        if (!File::exists($jsonPath)) {
            $subDirs = File::directories($tmpPath);
            if (count($subDirs) === 1 && File::exists($subDirs[0] . '/theme.json')) {
                $jsonPath = $subDirs[0] . '/theme.json';
                $sourceDir = $subDirs[0];
            } else {
                File::deleteDirectory($tmpPath);
                throw new \Exception(__('admin/themes.extraction.manifest_missing'));
            }
        }

        $manifest = json_decode(File::get($jsonPath), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($manifest['type'], $manifest['provider'], $manifest['name'])) {
            File::deleteDirectory($tmpPath);
            throw new \Exception(__('admin/themes.extraction.manifest_invalid'));
        }

        return [
            'manifest' => $manifest,
            'source_dir' => $sourceDir,
            'tmp_path' => $tmpPath,
        ];
    }
}