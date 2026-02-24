<?php

return [
    'name_label' => 'Name',
    'name_helper' => 'Enter a unique name to identify this instance.',
    'provider_label' => 'Provider',
    'provider_helper' => 'Select the gateway provider to use for this instance.',
    'is_active_label' => 'Is Active?',
    'is_active_helper' => 'Toggle to enable or disable this gateway instance.',
    'version_label' => 'Version',
    'author_label' => 'Author',

    'delete' => [
        'in_use' => 'This gateway cannot be deleted because it is associated with existing invoices or transactions.',
    ],

    'disable' => [
        'in_use' => 'This gateway cannot be disabled because it is currently in use.',
    ],
];