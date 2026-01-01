<?php

return [
    'tabs' => [
        'summary' => 'Summary',
        'options' => 'Options',
    ],

    'name_label' => 'Name',
    'name_helper' => 'Enter the name of the package variant.',
    'description_label' => 'Description',
    'description_helper' => 'Internal notes or description for this variant. This content is only visible to admin.',
    'type_label' => 'Type',
    'type_helper' => 'Select the type of this variant.',
    'code_label' => 'Code',
    'code_helper' => 'Enter a identifier used by the system to reference this item. This value should be short and consistent.',
    'status_label' => 'Status',
    'status_helper' => 'Set the status of the variant to visible or hidden.',
    'status_options' => [
        'visible' => 'Visible',
        'hidden' => 'Hidden',
    ],
    'is_upgradable_label' => 'Is Upgradable?',
    'is_upgradable_helper' => 'Enable to allow this variant to be upgraded or changed after purchase.',
    'package_label' => 'Package',
    'package_helper' => 'Select the package this variant belongs to.',
];