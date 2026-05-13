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

    'updating_title' => 'Updating Billmora',
    'updating_subtitle' => 'Applying update :version — please do not close this page.',
    'update_success' => 'Successfully updated to v:version!',
    'update_failed' => 'Update failed. Maintenance mode has been disabled. Please check the logs above.',
    'back_to_update' => 'Back to Update Page',
];
