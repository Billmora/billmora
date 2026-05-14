<?php

return [
    'title' => 'System Update',
    'check_complete' => 'Version check completed.',
    'no_update' => 'Your system is already up to date.',
    'requirements_not_met' => 'System requirements are not met. Please resolve the issues before updating.',

    'version' => [
        'current' => 'Current Version',
        'latest' => 'Latest Version',
        'up_to_date' => 'Up to Date',
        'update_available' => 'Update Available',
        'unknown' => 'Unable to Check',
    ],

    'release' => [
        'title' => "What's New in :version",
        'published' => 'Published on :date',
        'view_github' => 'View on GitHub',
        'no_notes' => 'No release notes available.',
    ],

    'requirements' => [
        'title' => 'System Requirements',
        'description' => 'All requirements must be satisfied before updating.',
        'label' => 'Requirement',
        'required' => 'Required',
        'current' => 'Current',
        'status' => 'Status',
        'passed' => 'Passed',
        'failed' => 'Failed',
        'items' => [
            'php_version' => 'PHP Version',
            'phar_extension' => 'Phar Extension',
            'composer' => 'Composer',
            'disk_space' => 'Available Disk Space',
            'writable' => 'Application Directory',
        ],
        'values' => [
            'enabled' => 'Enabled',
            'disabled' => 'Disabled',
            'available' => 'Available',
            'not_found' => 'Not Found',
            'writable' => 'Writable',
            'read_only' => 'Read Only',
        ],
    ],

    'actions' => [
        'check' => 'Check for Updates',
        'update' => 'Update to :version',
        'confirm_title' => 'Confirm Update',
        'confirm_message' => 'Are you sure you want to update to :version? Please ensure you have backed up your database before proceeding. The system will enter maintenance mode during the update.',
        'confirm_button' => 'Yes, Update Now',
        'cancel_button' => 'Cancel',
    ],

    'warning' => [
        'title' => 'Before You Update',
        'backup' => 'Please make sure to backup your database and application files before proceeding with the update.',
    ],

    'maintenance_message' => 'System is being updated. Please check back shortly.',

    'steps' => [
        'no_update' => 'No update available.',
        'maintenance_enabling' => 'Enabling maintenance mode...',
        'maintenance_enabled' => 'Maintenance mode enabled.',
        'downloading' => 'Downloading release v:version...',
        'download_complete' => 'Download complete.',
        'extracting' => 'Extracting files...',
        'extracted' => 'Files extracted and applied.',
        'composer_installing' => 'Installing dependencies (composer install)...',
        'composer_installed' => 'Dependencies installed.',
        'migrations_running' => 'Running database migrations...',
        'migrations_complete' => 'Migrations complete.',
        'cache_clearing' => 'Clearing application cache...',
        'cache_cleared' => 'Cache cleared.',
        'optimizing' => 'Optimizing application...',
        'optimized' => 'Optimization complete.',
        'queue_restarting' => 'Restarting queue workers...',
        'queue_restarted' => 'Queue workers restarted.',
        'maintenance_disabling' => 'Disabling maintenance mode...',
        'maintenance_disabled' => 'Maintenance mode disabled.',
        'cleanup' => 'Cleaning up temporary files...',
        'cleanup_complete' => 'Cleanup complete.',
        'completed' => 'Update to v:version completed successfully!',
        'error' => 'Error: :message',
        'maintenance_disabled_after_error' => 'Maintenance mode disabled after error.',
    ],

    'updating_title' => 'Updating Billmora',
    'updating_subtitle' => 'Applying update :version — please do not close this page.',
    'update_success' => 'Successfully updated to v:version!',
    'update_failed' => 'Update failed. Maintenance mode has been disabled. Please check the logs above.',
    'update_already_running' => 'An update is already in progress.',
    'back_to_update' => 'Back to Update Page',

    'progress' => [
        'running' => 'In Progress',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'log_title' => 'Update Log',
        'log_description' => 'Real-time update process output.',
        'waiting' => 'Waiting for update process to start...',
        'success_title' => 'Update Successful',
        'success_message' => 'The system has been successfully updated to v:version.',
        'failed_title' => 'Update Failed',
        'failed_message' => 'The update process encountered an error. Maintenance mode has been disabled automatically. Please check the logs above for details.',
    ],
];
