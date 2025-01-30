<?php

if (!function_exists('billconf')) {
    function billconf(string $key, $default = null)
    {
        return config($key, $default);
    }
}
