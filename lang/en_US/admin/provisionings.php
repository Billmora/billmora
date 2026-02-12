<?php

return [
    'name_label' => 'Name',
    'instance_count_label' => 'Instance Count',
    'version_label' => 'Version',
    'author_label' => 'Author',

    'upload' => [
        'instruction' => 'Click to upload or drag and drop',
        'type_hint' => 'ZIP File (Plugin)',
        'selected_prefix' => 'Selected: ',
        'replace_hint' => '(Drop another file to replace)',
    ],

    'install' => [
        'success' => 'Plugin :name (v:version) installed successfully!',
    ],

    'uninstall' => [
        'active_instances' => 'Cannot uninstall driver :driver. There are :count active instances using this driver.',
        'folder_missing' => 'Driver folder :driver not found.',
    ],

    'instance' => [
        'name_label' => 'Name',
        'name_helper' => 'Enter a unique name to identify this instance.',
        'provisioning_label' => 'Provisioning',
        'provisioning_helper' => 'Provisioning system assigned to this instance (read-only).',
        'is_active_label' => 'Is Active?',
        'is_active_helper' => 'Enable or disable this instance from being accessible.',
        'test_connection_label' => 'Test Connection',

        'driver_not_found' => 'Driver :driver not found.',
        'class_not_found' => 'Class :name not found.',
        'delete_in_use' => 'Cannot delete instance :name. It is currently assigned to :count active services.',
    ],

    'connection' => [
        'success' => 'Connection successful.',
        'failed' => 'Connection failed: :message',
    ],

    'plugin' => [
        'zip_failed' => 'Failed to open ZIP file.',
        'manifest_missing' => 'Invalid plugin: manifest.json not found in the zip file.',
        'manifest_invalid' => 'Invalid manifest.json: Missing name, type, or driver.',
        'driver_format' => 'Invalid driver format :driver: Must be PascalCase without spaces.',
        'type_mismatch' => 'Type mismatch: Expected :expected, got :current.',
        'driver_exists' => 'Plugin driver :driver already exists.',
    ],
];