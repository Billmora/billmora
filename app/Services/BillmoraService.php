<?php
namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BillmoraService
{
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return \DB::table('settings')->where('key', $key)->value('value') ?? $default;
    }

    public function setSetting(array $data): void
    {
        $validated = $this->validateData($data);

        foreach ($validated as $key => $value) {
            \DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
        }
    }

    public function setEnv(array $data): void
    {
        $validated = $this->validateData($data);
        $path = base_path('.env');

        if (File::exists($path)) {
            $env = File::get($path);

            foreach ($validated as $key => $value) {
                $formattedValue = $this->formatEnv($value);

                if (preg_match("/^{$key}=.*/m", $env)) {
                    $env = preg_replace("/^{$key}=.*/m", "{$key}={$formattedValue}", $env);
                } else {
                    $env .= "\n{$key}={$formattedValue}";
                }
            }

            File::put($path, $env);
            Artisan::call('config:clear');
        }
    }

    private function validateData(array $data): array
    {
        $validator = Validator::make($data, [
            '*.key' => 'required|string|max:255',
            '*.value' => 'nullable',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return collect($data)->pluck('value', 'key')->toArray();
    }

    private function formatEnv(mixed $value): string
    {
        return match (gettype($value)) {
            'boolean' => $value ? 'true' : 'false',
            'integer', 'double' => (string) $value,
            'array', 'object' => '"' . json_encode($value) . '"',
            default => "\"{$value}\"",
        };
    }
}
