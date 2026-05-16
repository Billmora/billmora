<?php

return [
    'name_label' => 'Name',
    'name_helper' => 'Enter a unique name to identify this instance.',
    'provider_label' => 'Provider',
    'provider_helper' => 'Select the provisioning provider to use for this instance.',
    'is_active_label' => 'Is Active?',
    'is_active_helper' => 'Toggle to enable or disable this provisioning instance.',
    'version_label' => 'Version',
    'author_label' => 'Author',
    'test_connection' => 'Test Connection',

    'connection' => [
        'success' => 'Connection successful',
        'failed' => 'Connection failed: :message',
    ],

    'provider' => [
        'missing' => 'No provisioning provider assigned to this service.',
        'class_missing' => 'provider class for :provider not found.',
    ],

    'delete' => [
        'in_use' => 'This plugin cannot be deleted because it is currently used by an active package or service.',
    ],
];