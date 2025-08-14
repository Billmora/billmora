<?php

return [
    'title' => 'Mail settings',
    'description' => 'Configure mail settings for the system.',
    'tabs' => [
        'mailer' => 'Mailer',
    ],

    'mailer_alert_label' => 'Important: Mailer Settings Source',
    'mailer_alert_helper' => 'Mailer settings are stored and retrieved from the ".env" file. Changes made here will override the current ".env" configuration until it is updated.',
    'mailer_driver_label' => 'Mailer Driver',
    'mailer_driver_helper' => 'Select the email sending method (driver).',
    'mailer_from_address_label' => 'Mailer From Address',
    'mailer_from_address_helper' => 'Enter the email address that will appear as the sender in outgoing emails.',
    'mailer_from_name_label' => 'Mailer From Name',
    'mailer_from_name_helper' => 'Enter the sender name that will appear in outgoing emails.',
    'mailer_smtp_host_label' => 'SMTP Host',
    'mailer_smtp_host_helper' => 'Enter your SMTP server address.',
    'mailer_smtp_port_label' => 'SMTP Port',
    'mailer_smtp_port_helper' => 'Enter the port number used by your SMTP server.',
    'mailer_smtp_encryption_label' => 'SMTP Encryption',
    'mailer_smtp_encryption_helper' => 'Select the encryption method for secure email delivery.',
    'mailer_smtp_username_label' => 'SMTP Username',
    'mailer_smtp_username_helper' => 'Enter the username for authenticating with your SMTP server.',
    'mailer_smtp_password_label' => 'SMTP Password',
    'mailer_smtp_password_helper' => 'Enter the password for authenticating with your SMTP server.',
    'mailer_mailgun_domain_label' => 'Mailgun Domain',
    'mailer_mailgun_domain_helper' => 'Enter your Mailgun domain.',
    'mailer_mailgun_secret_label' => 'Mailgun Secret',
    'mailer_mailgun_secret_helper' => 'Enter your Mailgun API key for authentication.',
    'mailer_mailgun_endpoint_label' => 'Mailgun Endpoint',
    'mailer_mailgun_endpoint_helper' => 'Enter your Mailgun API endpoint.',
];