<?php

return [
    'title' => 'API',
    'description' => 'Manage API tokens and access.',

    'token_name_label' => 'Token Name',
    'token_name_helper' => 'A friendly name to identify this token.',
    'rate_limit_label' => 'Rate Limit',
    'rate_limit_helper' => 'Maximum requests per minute for this token.',
    'expires_at_label' => 'Expires At',
    'expires_at_helper' => 'Leave empty for a token that never expires.',
    'whitelist_ips_label' => 'Whitelist IP Addresses',
    'whitelist_ips_helper' => 'Comma-separated IP addresses. Leave empty to allow all IPs.',
    'permissions_label' => 'Permissions',
    'permissions_helper' => 'Select which resources and actions this token can access. If none selected, all permissions are granted.',
    'last_used_label' => 'Last Used',

    'token_generated' => "Your API token has been generated. Copy it now — it won't be shown again.",
    'token_created_success' => 'API token created successfully.',
    'token_regenerated_success' => 'API token regenerated successfully. Copy the new token above.',
    'no_tokens' => 'No API tokens have been created yet.',
    'all_permissions' => 'All',
    'all_ips' => 'All',
    'never' => 'Never',

    'regenerate' => 'Regenerate',
    'regenerate_title' => 'Regenerate Token',
    'regenerate_description' => 'This will revoke the current token for :name and create a new one with the same settings. Any applications using the old token will lose access.',
];
