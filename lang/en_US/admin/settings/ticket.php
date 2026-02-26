<?php

return [
    'tabs' => [
        'ticketing' => 'Ticketing',
        'piping' => 'Piping',
    ],
    'title' => 'Ticket settings',
    'description' => 'Configure ticket settings for the system.',

    'ticketing_departements_label' => 'Ticket Departments',
    'ticketing_departements_helper' => 'Add a list of departments available for ticket categorization.',
    'ticketing_allow_client_close_label' => 'Allow Client to Close Ticket',
    'ticketing_allow_client_close_helper' => 'Enable this to allow clients to close their own tickets.',
    'ticketing_number_increment_label' => 'Ticket Number Increment',
    'ticketing_number_increment_helper' => 'Set the increment value for ticket numbers.',
    'ticketing_number_padding_label' => 'Ticket Number Padding',
    'ticketing_number_padding_helper' => 'Set the number of digits to pad ticket numbers with leading zeros.',
    'ticketing_number_format_label' => 'Ticket Number Format',
    'ticketing_number_format_helper' => 'Define the format for ticket numbers using {number} as a required placeholder. Optional placeholders: {day}, {month}, {year}.',

    'piping_enabled_label' => 'Enable Email Piping',
    'piping_enabled_helper' => 'Enable this to allow incoming emails to be converted into support tickets automatically.',
    'piping_mail_host_label' => 'Mail Host',
    'piping_mail_host_helper' => 'Enter the mail server host used for email piping (e.g. mail.example.com).',
    'piping_mail_port_label' => 'Mail Port',
    'piping_mail_port_helper' => 'Enter the port number for the mail server (e.g. 993 for IMAP SSL).',
    'piping_mail_address_label' => 'Mail Address',
    'piping_mail_address_helper' => 'Enter the email address that will receive and pipe incoming emails into tickets.',
    'piping_mail_password_label' => 'Mail Password',
    'piping_mail_password_helper' => 'Enter the password for the piping mail account.',
];