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
            throw new Exception("Failed to open ZIP file");
        }

        $jsonContent = $zip->getFromName('manifest.json');
        
        if (!$jsonContent) {
            $zip->close();
            throw new Exception("Invalid plugin: manifest.json not found in root");
        }

        $metadata = json_decode($jsonContent, true);

        if (!isset($metadata['name'], $metadata['type'], $metadata['driver'])) {
            $zip->close();
            throw new Exception("Invalid manifest.json: Missing name, type, or driver");
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $metadata['driver'])) {
            $zip->close();
            throw new Exception("Invalid driver format {$metadata['driver']}: Must be PascalCase without spaces");
        }

        if (strtolower($metadata['type']) !== strtolower($expectedType)) {
            $zip->close();
            throw new Exception("Type mismatch: Expected {$expectedType}, got {$metadata['type']}");
        }

        $targetPath = base_path("plugin/" . ucfirst($expectedType) . "/" . $metadata['driver']);
        
        if (File::exists($targetPath)) {
            $zip->close();
            throw new Exception("Plugin driver {$metadata['driver']} already exists");
        }

        File::makeDirectory($targetPath, 0755, true);
        $zip->extractTo($targetPath);
        $zip->close();

        return $metadata;
    }
}