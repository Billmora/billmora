<?php

return [
    'name_label' => 'Name',
    'name_helper' => 'Enter a unique name to identify this registrar instance.',
    'provider_label' => 'Provider',
    'provider_helper' => 'Select the registrar provider for this instance.',
    'is_active_label' => 'Is Active?',
    'is_active_helper' => 'Toggle to enable or disable this registrar instance.',
    'version_label' => 'Version',
    'author_label' => 'Author',
    'test_connection' => 'Test Connection',

    'connection' => [
        'success' => 'Connection successful',
        'failed' => 'Connection failed: :message',
    ],

    'provider' => [
        'driver_missing' => 'No registrar provider assigned to this domain or TLD.',
        'driver_class_missing' => 'Provider class for :driver not found.',
        'disabled' => 'Registrar plugin :name is currently disabled.',
    ],

    'delete' => [
        'in_use' => 'This registrar cannot be deleted because it is currently used by an active TLD or registrant.',
    ],
];
