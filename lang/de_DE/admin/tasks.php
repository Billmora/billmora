<?php

return [
    'date_label' => 'Date',
    'event_label' => 'Event / Action',
    'target_label' => 'Target',
    'error_message_label' => 'Error Message',
    
    'target_system' => 'System',
    'unknown_error' => 'Unknown Error',
    
    'not_failed_state' => 'This task is not in a failed state or has already been resolved.',
    'retry_not_implemented' => 'Retry is not yet supported for this task type.',
    'missing_service_id' => 'Missing service ID in task properties. Cannot proceed.',
    'service_not_found' => 'The associated service no longer exists.',
    'no_provisioning_plugin' => 'The service does not have an active provisioning plugin assigned.',
    'action_not_supported' => 'The plugin does not support the :action action.',
    
    'retry' => 'Retry',
    'retry_failed' => 'Retry attempt failed: :message',
    'retry_resolved' => 'Provisioning action :action was successfully retried and resolved.',
    'dismiss' => 'Dismiss',
    'dismissed' => 'Task has been dismissed from the queue.',
];