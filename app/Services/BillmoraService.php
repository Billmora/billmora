<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BillmoraService
{
    public static function getSetting(string $key, mixed $default = null): mixed
    {
        return Setting::where('key', $key)->value('value') ?? $default;
    }

    public static function setSetting(array $data): void
    {
        $validated = self::validateData($data);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
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
        return match (gettype($value)) {
            'boolean' => $value ? 'true' : 'false',
            'integer', 'double' => (string) $value,
            'array', 'object' => '"' . json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '"',
            default => "\"{$value}\"",
        };
    }
}
