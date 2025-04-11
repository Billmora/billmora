<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BillmoraService
{
    public static function getSetting(string $category, string $key, mixed $default = null): mixed
    {
        static $tableExists = null;

        if ($tableExists === null) {
            $tableExists = Schema::hasTable('settings');
        }

        if (!$tableExists) {
            return $default;
        }
        
        return Setting::where('category', $category)
            ->where('key', $key)
            ->value('value') ?? $default;
    }

    public static function setSetting(string $category, array $data): void
    {
        $validated = self::validateData($data);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['category' => $category, 'key' => $key],
                ['value' => $value]
            );
        }
    }

    public static function getGeneral(string $key, mixed $default = null): mixed
    {
        return self::getSetting('general', $key, $default);
    }

    public static function setGeneral(array $data): void
    {
        self::setSetting('general', $data);
    }

    public static function getMail(string $key, mixed $default = null): mixed
    {
        return self::getSetting('mail', $key, $default);
    }

    public static function setMail(array $data): void
    {
        self::setSetting('mail', $data);
    }

    public static function setEnv(array $data): void
    {
        $validated = self::validateData($data);
        $path = base_path('.env');

        if (!File::exists($path)) {
            throw new \RuntimeException('.env file not found.');
        }

        $env = File::get($path);

        foreach ($validated as $key => $value) {
            $formattedValue = self::formatEnv($value);

            if (preg_match("/^{$key}=.*/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$formattedValue}", $env);
            } else {
                $env .= "\n{$key}={$formattedValue}";
            }
        }

        File::put($path, $env);
        Artisan::call('config:clear');
    }

    private static function validateData(array $data): array
    {
        $validator = Validator::make(['keys' => array_keys($data)], [
            'keys.*' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $data;
    }

    private static function formatEnv(mixed $value): string
    {
        if (is_null($value) || $value === '') {
            return '';
        }
    
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
    
        if (is_numeric($value)) {
            return (string) $value;
        }
    
        if (is_string($value) && preg_match('/[^\w.\-+\/]/', $value)) {
            return sprintf('"%s"', addcslashes($value, '\\"'));
        }
    
        return $value;
    }
}
