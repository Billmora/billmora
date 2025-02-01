<?php
namespace App\Helpers;

use App\Models\Setting;

class Config
{
    public static function setting(string $key, $value = null)
    {
        $setting = Setting::where('key', $key)->first();
        
        if ($setting) {
            return $setting->value;
        }

        return config($key, $value);
    }
}
