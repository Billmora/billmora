<?php

return [
    'name_label' => 'Name',
    'provider_label' => 'Provider',
    'type_label' => 'Type',
    'version_label' => 'Version',
    'author_label' => 'Author',
    'folder_label' => 'Folder',

    'active_theme' => 'Active Theme',
    'theme_label' => ':type Theme',
    'theme_helper' => 'Choose the theme you want to activate for the :type area',

    'configure' => [
        'not_provide' => 'This theme does not provide a custom configuration page.'
    ],
    
    'install' => [
        'already_exists' => 'Theme :provider is already installed. Use the update feature instead.',
        'success' => 'Theme :name has been installed successfully.',
    ],

    'update' => [
        'mismatch' => 'The uploaded theme does not match the target theme. Expected: :target, Uploaded: :uploaded.',
        'success' => 'Theme :name has been updated successfully.',
    ],

    'uninstall' => [
        'core_protected' => 'Core themes cannot be uninstalled.',
        'active_protected' => 'Cannot uninstall an active theme. Please activate another theme first.',
        'success' => 'Theme :name has been uninstalled successfully.',
    ],

    'extraction' => [
        'corrupted_zip' => 'Failed to extract: The ZIP file appears to be corrupted or invalid.',
        'manifest_missing' => 'Invalid theme format: theme.json is missing from the ZIP archive.',
        'manifest_invalid' => 'Invalid theme.json: Missing required fields (name, type, provider) or JSON is malformed.',
    ],

    'upload' => [
        'instruction' => 'Click to upload or drag and drop',
        'type_hint' => 'ZIP File (Theme)',
        'selected_prefix' => 'Selected: ',
        'replace_hint' => '(Drop another file to replace)',
    ],
];