<?php

return [
    'name_label' => 'Name',
    'type_label' => 'Type',
    'version_label' => 'Version',
    'author_label' => 'Author',

    'provider' => [
        'files_missing' => 'Provider files for :provider not found.',
    ],

    'install' => [
        'already_exists' => 'The :provider plugin already exists on the server. Please use the update feature instead.',
        'success' => 'Plugin :name has been successfully installed.',
    ],

    'update' => [
        'mismatch' => 'ZIP file mismatch! You uploaded :uploaded, but are trying to update :target.',
        'not_installed' => 'Plugin directory does not exist. Please install the plugin first.',
        'success' => 'Plugin :name has been successfully updated to version :version.',
    ],

    'uninstall' => [
        'invalid_identifier' => 'Invalid plugin identifier format.',
        'still_registered' => 'Cannot delete files! The :provider plugin is still registered in the database.',
        'directory_not_found' => 'Plugin directory not found.',
        'success' => 'Plugin files for :provider have been permanently uninstalled.',
    ],

    'extraction' => [
        'corrupted_zip' => 'The ZIP file is corrupted or cannot be read.',
        'manifest_missing' => 'Extraction failed: plugin.json not found inside the ZIP file.',
        'manifest_invalid' => 'Extraction failed: plugin.json is invalid or missing required parameters (type, provider, version).',
    ],

    'upload' => [
        'instruction' => 'Click to upload or drag and drop',
        'type_hint' => 'ZIP File (Plugin)',
        'selected_prefix' => 'Selected: ',
        'replace_hint' => '(Drop another file to replace)',
    ],
];