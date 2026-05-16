<?php

return [
    'title' => 'System Health',
    'database' => 'Database',
    'cache' => 'Cache',
    'environment' => 'Environment',
    'debug_mode' => 'Debug Mode',
    'version' => 'Version',
    'current_version' => 'Current Version',
    'latest_version' => 'Latest Version',
    'php_version' => 'PHP Version',
    'laravel_version' => 'Framework Version',
    'status' => [
        'ok' => 'Operational',
        'issue' => 'Issue Detected',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'up_to_date' => 'Up to Date',
        'update_available' => 'Update Available',
        'unknown' => 'Unknown',
    ],
    'cards' => [
        'database_desc' => 'Database connection status and availability.',
        'cache_desc' => 'Cache driver performance and connectivity.',
        'env_desc' => 'Application running environment.',
        'debug_desc' => 'Diagnostic mode for debugging and logging.',
        'version_desc' => 'Real-time version comparison with official repository.',
        'php_desc' => 'Runtime engine version for PHP.',
        'laravel_desc' => 'Current version of the Laravel framework.',
    ]
];
