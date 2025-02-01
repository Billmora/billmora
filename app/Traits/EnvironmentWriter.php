<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\File;

trait EnvironmentWriter
{
    private function writeToEnv(array $data)
    {
        $path = base_path('.env');
        
        if (!File::exists($path)) {
            throw new Exception(".env file not found! Ensure the .env file is located correctly.");
        }

        $env = File::get($path);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}=" . (is_null($value) ? 'null' : '"' . addslashes($value) . '"');

            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, $replacement, $env);
            } else {
                $env .= "\n{$replacement}";
            }
        }

        File::put($path, $env);
    }
}