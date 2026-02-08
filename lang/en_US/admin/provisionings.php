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
        'driver_not_found' => 'Driver :driver not found.',
        'class_not_found' => 'Class :name not found.',
        'delete_in_use' => 'Cannot delete instance :name. It is currently assigned to :count active services.',
    ],

    'connection' => [
        'success' => 'Connection successful.',
        'failed' => 'Connection failed: ',
    ],
];