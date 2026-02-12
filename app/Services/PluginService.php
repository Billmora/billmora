<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Exception;

class PluginService
{
    /**
     * Install a plugin from uploaded ZIP file with validation and extraction.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $expectedType
     * @return array
     * @throws \Exception
     */
    public function install(UploadedFile $file, string $expectedType): array
    {
        $zip = new ZipArchive;
        if ($zip->open($file->path()) !== true) {
            throw new Exception(__('admin/provisionings.plugin.zip_failed'));
        }

        $jsonContent = $zip->getFromName('manifest.json');
        
        if (!$jsonContent) {
            $zip->close();
            throw new Exception(__('admin/provisionings.plugin.manifest_missing'));
        }

        $metadata = json_decode($jsonContent, true);

        if (!isset($metadata['name'], $metadata['type'], $metadata['driver'])) {
            $zip->close();
            throw new Exception(__('admin/provisionings.plugin.manifest_invalid'));
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $metadata['driver'])) {
            $zip->close();
            throw new Exception(__('admin/provisionings.plugin.driver_format', [
                'driver' => $metadata['driver']
            ]));
        }

        if (strtolower($metadata['type']) !== strtolower($expectedType)) {
            $zip->close();
            throw new Exception(__('admin/provisionings.plugin.type_mismatch', [
                'expected' => $expectedType,
                'current' => $metadata['type']
            ]));
        }

        $targetPath = base_path("plugin/" . ucfirst($expectedType) . "/" . $metadata['driver']);
        
        if (File::exists($targetPath)) {
            $zip->close();
            throw new Exception(__('admin/provisionings.plugin.driver_exists', [
                'driver' => $metadata['driver']
            ]));
        }

        File::makeDirectory($targetPath, 0755, true);
        $zip->extractTo($targetPath);
        $zip->close();

        return $metadata;
    }

    /**
     * Uninstall a plugin by removing its directory.
     *
     * @param string $driver
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function uninstall(string $driver, string $type): array
    {
        if (!preg_match('/^[a-zA-Z0-9]+$/', $driver)) {
            throw new Exception(__('admin/provisionings.plugin.driver_format', ['driver' => $driver]));
        }

        $targetPath = base_path("plugin/" . ucfirst($type) . "/" . $driver);

        if (!File::exists($targetPath)) {
            throw new Exception(__('admin/provisionings.uninstall.folder_missing', ['driver' => $driver]));
        }

        $manifestPath = $targetPath . '/manifest.json';
        $metadata = ['driver' => $driver];

        if (File::exists($manifestPath)) {
            $content = File::get($manifestPath);
            $decoded = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                $metadata = $decoded;
            }
        }

        if (!File::deleteDirectory($targetPath)) {
            throw new Exception(__('admin/provisionings.uninstall.directory_access'));
        }

        return $metadata;
    }
}