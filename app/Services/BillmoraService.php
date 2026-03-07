<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BillmoraService
{

    /**
     * Cache key prefix for settings.
     */
    protected const CACHE_PREFIX = 'setting_';

    /**
     * Cache time-to-live for settings (in seconds).
     */
    protected const CACHE_TTL = 86400;

    /**
     * Retrieve a setting value by category and key with caching.
     *
     * @param  string  $category   The settings category (e.g. "general").
     * @param  string  $key        The key inside the specified category.
     * @param  mixed   $default    Default value to return if setting is not found.
     * @return mixed               The stored value or default if not found.
     */
    public static function getSetting(string $category, string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $category . '_' . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($category, $key, $default) {
            return Setting::where('category', $category)
                ->where('key', $key)
                ->value('value') ?? $default;
        });
    }

    /**
     * Save or update multiple settings within a category,
     * clearing their cached version after upserting.
     *
     * @param  string  $category   The settings category being saved (e.g. "general").
     * @param  array   $data       Key/value pairs of settings to persist.
     * @return void
     */
    public static function setSetting(string $category, array $data): void
    {
        $validated = self::validateData($data);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['category' => $category, 'key' => $key],
                ['value' => $value]
            );
            
            Cache::forget(self::CACHE_PREFIX . $category . '_' . $key);
        }
    }

    /**
     * Validate array keys for settings before saving.
     *
     * @param  array $data   Key/value pairs provided.
     * @return array         The original data if valid.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private static function validateData(array $data): array
    {
        $validator = Validator::make(['keys' => array_keys($data)], [
            'keys.*' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $data;
    }
    
    /**
     * Retrieve a general setting value by its key.
     *
     * This is a shortcut method that internally calls 'getSetting()' using
     * the 'general' category.
     *
     * @param string $key     The key of the setting to retrieve.
     * @param mixed  $default The default value to return if the setting is not found.
     *
     * @return mixed The value of the setting or the default if not found.
     */
    public static function getGeneral(string $key, mixed $default = null): mixed
    {
        return self::getSetting('general', $key, $default);
    }

    /**
     * Store or update general settings.
     *
     * This is a shortcut method that internally calls 'setSetting()' using
     * the 'general' category.
     *
     * @param array $data An associative array of key-value pairs to store or update.
     *
     * @return void
     */
    public static function setGeneral(array $data): void
    {
        self::setSetting('general', $data);
    }

    /**
     * Retrieve a mail setting value by its key.
     *
     * This is a shortcut method that internally calls 'getSetting()' using
     * the 'mail' category.
     *
     * @param string $key     The key of the setting to retrieve.
     * @param mixed  $default The default value to return if the setting is not found.
     *
     * @return mixed The value of the setting or the default if not found.
     */
    public static function getMail(string $key, mixed $default = null): mixed
    {
        return self::getSetting('mail', $key, $default);
    }

    /**
     * Store or update mail settings.
     *
     * This is a shortcut method that internally calls 'setSetting()' using
     * the 'mail' category.
     *
     * @param array $data An associative array of key-value pairs to store or update.
     *
     * @return void
     */
    public static function setMail(array $data): void
    {
        self::setSetting('mail', $data);
    }

    /**
     * Retrieve a auth setting value by its key.
     *
     * This is a shortcut method that internally calls 'getAuth()' using
     * the 'auth' category.
     *
     * @param string $key     The key of the setting to retrieve.
     * @param mixed  $default The default value to return if the setting is not found.
     *
     * @return mixed The value of the setting or the default if not found.
     */
    public static function getAuth(string $key, mixed $default = null): mixed
    {
        return self::getSetting('auth', $key, $default);
    }

    /**
     * Determine if the given authentication key contains the specified value(s).
     *
     * @param string       $key   The authentication key to check.
     * @param string|array $value The value or list of values to compare against the stored authentication values.
     *
     * @return bool True if the value exists in the authentication settings, false otherwise.
     */
    public static function hasAuth(string $key, string|array $value): bool
    {
        $values = self::getAuth($key, []);

        if (!is_array($values)) {
            return false;
        }

        return is_array($value)
            ? !empty(array_intersect($value, $values))
            : in_array($value, $values, true);
    }

    /**
     * Store or update auth settings.
     *
     * This is a shortcut method that internally calls 'setAuth()' using
     * the 'auth' category.
     *
     * @param array $data An associative array of key-value pairs to store or update.
     *
     * @return void
     */
    public static function setAuth(array $data): void
    {
        self::setSetting('auth', $data);
    }

    /**
     * Retrieve a captcha setting value by its key.
     *
     * This is a shortcut method that internally calls 'getCaptcha()' using
     * the 'captcha' category.
     *
     * @param string $key     The key of the setting to retrieve.
     * @param mixed  $default The default value to return if the setting is not found.
     *
     * @return mixed The value of the setting or the default if not found.
     */
    public static function getCaptcha(string $key, mixed $default = null): mixed
    {
        return self::getSetting('captcha', $key, $default);
    }

    /**
     * Store or update captcha settings.
     *
     * This is a shortcut method that internally calls 'setCaptcha()' using
     * the 'captcha' category.
     *
     * @param array $data An associative array of key-value pairs to store or update.
     *
     * @return void
     */
    public static function setCaptcha(array $data): void
    {
        self::setSetting('captcha', $data);
    }

    /**
     * Retrieve a ticket setting value by its key.
     *
     * This is a shortcut method that internally calls 'getTicket()' using
     * the 'ticket' category.
     *
     * @param string $key     The key of the setting to retrieve.
     * @param mixed  $default The default value to return if the setting is not found.
     *
     * @return mixed The value of the setting or the default if not found.
     */
    public static function getTicket(string $key, mixed $default = null): mixed
    {
        return self::getSetting('ticket', $key, $default);
    }

    /**
     * Store or update ticket settings.
     *
     * This is a shortcut method that internally calls 'setTicket()' using
     * the 'ticket' category.
     *
     * @param array $data An associative array of key-value pairs to store or update.
     *
     * @return void
     */
    public static function setTicket(array $data): void
    {
        self::setSetting('ticket', $data);
    }

    /**
     * Retrieve a automation setting value by its key.
     *
     * This is a shortcut method that internally calls 'getAutomation()' using
     * the 'automation' category.
     *
     * @param string $key     The key of the setting to retrieve.
     * @param mixed  $default The default value to return if the setting is not found.
     *
     * @return mixed The value of the setting or the default if not found.
     */
    public static function getAutomation(string $key, mixed $default = null): mixed
    {
        return self::getSetting('automation', $key, $default);
    }

    /**
     * Store or update automation settings.
     *
     * This is a shortcut method that internally calls 'setAutomation()' using
     * the 'automation' category.
     *
     * @param array $data An associative array of key-value pairs to store or update.
     *
     * @return void
     */
    public static function setAutomation(array $data): void
    {
        self::setSetting('automation', $data);
    }

    /**
     * Update or append environment variables in the '.env' file.
     *
     * This method accepts an array of key-value pairs and updates or appends them to the '.env' file.
     * If a key already exists, its value will be replaced; otherwise, it will be appended.
     *
     * @param array<string, mixed> $data  The environment variables to set.
     *
     * @throws \RuntimeException If the '.env' file does not exist.
     */
    public static function setEnv(array $data): void
    {
        $validated = self::validateData($data);
        $path = base_path('.env');

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

    /**
     * Format a value for use in a '.env' file.
     *
     * This method ensures the value is properly escaped and wrapped in quotes if needed.
     * Handles booleans, nulls, numbers, and strings with special characters.
     *
     * @param mixed $value The value to format.
     *
     * @return string The formatted value ready for insertion into the '.env' file.
     */
    private static function formatEnv(mixed $value): string
    {
        if (empty($value)) {
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