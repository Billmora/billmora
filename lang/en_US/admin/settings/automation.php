<?php

return [
    'tabs' => [
        'scheduling' => 'Scheduling',
        'billing' => 'Billing',
        'service' => 'Service',
        'ticket' => 'Ticket',
    ],
    'title' => 'Automation settings',
    'description' => 'Configure automation settings for the system.',

    'time_of_day_label' => 'Automation Time of Day',
    'time_of_day_helper' => 'The time of day you want the daily automation cron job to execute (e.g., 01:00).',
    'prune_email_history_days_label' => 'Prune Email History',
    'prune_email_history_days_helper' => 'Enter the number of days to retain the sent email history (Leave 0 to never delete).',
    'prune_system_logs_days_label' => 'Prune System Logs',
    'prune_system_logs_days_helper' => 'Enter the number of days to retain system audit logs (Leave 0 to never delete).',

    'invoice_generation_days_label' => 'Invoice Generation',
    'invoice_generation_days_helper' => 'Enter the default number of days before the due date to generate recurring invoices.',
    'invoice_reminder_days_label' => 'Unpaid Reminder',
    'invoice_reminder_days_helper' => 'Enter the number of days before the invoice due date you would like to send a reminder.',
    'invoice_overdue_first_days_label' => 'First Overdue Reminder',
    'invoice_overdue_first_days_helper' => 'Enter the number of days after the invoice due date to send the first overdue notice.',
    'invoice_overdue_second_days_label' => 'Second Overdue Reminder',
    'invoice_overdue_second_days_helper' => 'Enter the number of days after the invoice due date to send the second overdue notice.',
    'invoice_overdue_third_days_label' => 'Third Overdue Reminder',
    'invoice_overdue_third_days_helper' => 'Enter the number of days after the invoice due date to send the third (final) overdue notice.',
    'invoice_late_fee_days_label' => 'Add Late Fee Days',
    'invoice_late_fee_days_helper' => 'Enter the number of days after the due payment date you want to add the late fee (Leave 0 to disable).',
    'invoice_auto_cancel_days_label' => 'Auto Cancel Unpaid Invoices',
    'invoice_auto_cancel_days_helper' => 'Cancel unpaid invoices automatically after this many days overdue (Leave 0 to disable).',
    
    'service_suspend_days_label' => 'Auto Suspension Days',
    'service_suspend_days_helper' => 'Enter the number of days after the due payment date you want to wait before automatically suspending the service.',
    'service_terminate_days_label' => 'Auto Termination Days',
    'service_terminate_days_helper' => 'Enter the number of days after the due payment date you want to wait before permanently terminating the service.',
    'auto_accept_cancellation_label' => 'Auto Accept Cancellation Requests',
    'auto_accept_cancellation_helper' => 'Automatically process cancellation requests. Immediate requests are terminated on the next cron run, while End of Period requests are terminated when their next due date is reached.',
    
    'ticket_close_days_label' => 'Auto Close Inactive Tickets',
    'ticket_close_days_helper' => 'Enter the number of days since the last reply before automatically closing tickets (Leave 0 to disable).',
    'prune_ticket_attachments_days_label' => 'Prune Ticket Attachments',
    'prune_ticket_attachments_days_helper' => 'Enter the number of days after a ticket is CLOSED to automatically delete its attachments to save disk space (Leave 0 to never delete).',
];