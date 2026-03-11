<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ZipArchive;

class PluginsController extends Controller
{
    /**
     * Applies permission-based middleware for accessing plugins.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:plugins.view')->only(['index']);
        $this->middleware('permission:plugins.install')->only(['install']);
        $this->middleware('permission:plugins.update')->only(['update']);
        $this->middleware('permission:plugins.uninstall')->only(['uninstall']);
    }

    /**
     * Display a list of all installed plugins discovered from the filesystem.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $plugins = [];
        $types = ['Gateways', 'Provisionings', 'Modules'];

        foreach ($types as $typeFolder) {
            $path = base_path("plugin/{$typeFolder}");

            if (File::exists($path)) {
                $directories = File::directories($path);

                foreach ($directories as $dir) {
                    $jsonPath = $dir . '/plugin.json';
                    
                    if (File::exists($jsonPath)) {
                        $manifest = json_decode(File::get($jsonPath), true);
                        
                        if (isset($manifest['type'], $manifest['provider'])) {
                            $type = strtolower($manifest['type']);
                            $provider = strtolower($manifest['provider']);
                            
                            $manifest['identifier'] = "{$type}.{$provider}";

                            $plugins[] = $manifest;
                        }
                    }
                }
            }
        }

        if ($search = $request->input('search')) {
            $plugins = collect($plugins)->filter(function ($plugin) use ($search) {
                return stripos($plugin['name'] ?? '', $search) !== false ||
                       stripos($plugin['type'] ?? '', $search) !== false ||
                       stripos($plugin['provider'] ?? '', $search) !== false ||
                       stripos($plugin['author'] ?? '', $search) !== false;
            })->values()->all();
        }

        return view('admin::plugins.index', compact('plugins'));
    }

    /**
     * Install a new plugin from an uploaded ZIP archive.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function install(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plugin_file' => ['required', 'file', 'mimes:zip', 'max:20480'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->errors()->first());
        }

        try {
            ['manifest' => $manifest, 'source_dir' => $sourceDir, 'tmp_path' => $tmpPath] = $this->extractAndValidateZip($request->file('plugin_file'));

            $typePlural = Str::plural(ucfirst($manifest['type']));
            $targetDir = base_path("plugin/{$typePlural}/" . ucfirst($manifest['provider']));

            if (File::exists($targetDir)) {
                File::deleteDirectory($tmpPath);
                return back()->with('error', __('admin/plugins.install.already_exists', [
                    'provider' => $manifest['provider']
                ]));
            }

            File::ensureDirectoryExists(base_path("plugin/{$typePlural}"));
            File::moveDirectory($sourceDir, $targetDir);
            File::deleteDirectory($tmpPath);

            Audit::system(
                Auth::id(), 
                'plugin.install', 
                $manifest
            );

            return back()->with('success', __('admin/plugins.install.success', [
                'name' => $manifest['name']
            ]));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an existing installed plugin from an uploaded ZIP archive.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $identifier  The plugin identifier in "{type}.{provider}" format (e.g. "gateway.stripe")
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $identifier)
    {
        $validator = Validator::make($request->all(), [
            'plugin_file' => ['required', 'file', 'mimes:zip', 'max:20480'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->errors()->first());
        }

        try {
            ['manifest' => $manifest, 'source_dir' => $sourceDir, 'tmp_path' => $tmpPath] = $this->extractAndValidateZip($request->file('plugin_file'));

            $uploadedIdentifier = strtolower($manifest['type']) . '.' . strtolower($manifest['provider']);

            if ($uploadedIdentifier !== strtolower($identifier)) {
                File::deleteDirectory($tmpPath);
                return back()->with('error', __('admin/plugins.update.mismatch', [
                    'uploaded' => $uploadedIdentifier,
                    'target' => strtolower($identifier)
                ]));
            }

            $typePlural = Str::plural(ucfirst($manifest['type']));
            $targetDir = base_path("plugin/{$typePlural}/" . ucfirst($manifest['provider']));

            if (!File::exists($targetDir)) {
                File::deleteDirectory($tmpPath);
                return back()->with('error', __('admin/plugins.update.not_installed'));
            }

            File::deleteDirectory($targetDir);
            File::moveDirectory($sourceDir, $targetDir);
            File::deleteDirectory($tmpPath);

            Audit::system(
                Auth::id(), 
                'plugin.update', 
                array_merge($manifest, ['previous_identifier' => $identifier])
            );

            return back()->with('success', __('admin/plugins.update.success', [
                'name' => $manifest['name'],
                'version' => $manifest['version']
            ]));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Uninstall a plugin by removing its directory from the filesystem.
     * Plugins that are still registered in the database cannot be uninstalled.
     *
     * @param string $identifier  The plugin identifier in "{type}.{provider}" format (e.g. "gateway.stripe")
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uninstall(string $identifier)
    {
        $parts = explode('.', $identifier);
        
        if (count($parts) !== 2) {
            return back()->with('error', __('admin/plugins.uninstall.invalid_identifier'));
        }

        $type = $parts[0];
        $provider = $parts[1];

        $isRegistered = Plugin::where('type', $type)->where('provider', $provider)->exists();

        if ($isRegistered) {
            return back()->with('error', __('admin/plugins.uninstall.still_registered', [
                'provider' => $provider
            ]));
        }

        $typePlural = Str::plural(ucfirst($type));
        $targetDir = base_path("plugin/{$typePlural}/" . ucfirst($provider));

        if (File::exists($targetDir)) {
            Audit::system(
                Auth::id(), 
                'plugin.uninstall', 
                [
                    'identifier' => $identifier,
                    'type' => $type,
                    'provider' => $provider
                ]
            );

            File::deleteDirectory($targetDir);
            return back()->with('success', __('admin/plugins.uninstall.success', [
                'provider' => $provider
            ]));
        }

        return back()->with('error', __('admin/plugins.uninstall.directory_not_found'));
    }

    /**
     * Extract and validate a plugin ZIP archive, returning the manifest and relevant paths.
     *
     * @param \Illuminate\Http\UploadedFile $zipFile
     * @return array{manifest: array, source_dir: string, tmp_path: string}
     *
     * @throws \Exception If the ZIP is corrupted, plugin.json is missing, or the manifest is invalid.
     */
    private function extractAndValidateZip($zipFile): array
    {
        $zip = new ZipArchive;
        $tmpPath = storage_path('app/tmp/plugins_' . uniqid());

        if ($zip->open($zipFile->path()) === true) {
            File::ensureDirectoryExists($tmpPath);
            $zip->extractTo($tmpPath);
            $zip->close();
        } else {
            throw new \Exception(__('admin/plugins.extraction.corrupted_zip'));
        }

        $jsonPath = $tmpPath . '/plugin.json';
        $sourceDir = $tmpPath;

        if (!File::exists($jsonPath)) {
            $subDirs = File::directories($tmpPath);
            if (count($subDirs) === 1 && File::exists($subDirs[0] . '/plugin.json')) {
                $jsonPath = $subDirs[0] . '/plugin.json';
                $sourceDir = $subDirs[0];
            } else {
                File::deleteDirectory($tmpPath);
                throw new \Exception(__('admin/plugins.extraction.manifest_missing'));
            }
        }

        $manifest = json_decode(File::get($jsonPath), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($manifest['type'], $manifest['provider'], $manifest['version'])) {
            File::deleteDirectory($tmpPath);
            throw new \Exception(__('admin/plugins.extraction.manifest_invalid'));
        }

        return [
            'manifest' => $manifest,
            'source_dir' => $sourceDir,
            'tmp_path' => $tmpPath,
        ];
    }
}