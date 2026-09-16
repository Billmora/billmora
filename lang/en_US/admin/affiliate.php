<?php

return [
    'title' => 'Affiliate',
    'overview_title' => 'Affiliate Overview',
    'members_title' => 'Affiliate Members',
    'commissions_title' => 'Affiliate Commissions',
    'withdrawals_title' => 'Affiliate Withdrawals',

    'tabs' => [
        'overview' => 'Overview',
        'members' => 'Members',
        'commissions' => 'Commissions',
        'withdrawals' => 'Withdrawals',
    ],

    'filters' => [
        'all' => 'All',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    'overview' => [
        'total_members' => 'Total Members',
        'active' => '(:count active)',
        'referrals' => 'Referrals',
        'converted' => '(:count converted)',
        'total_commissions' => 'Total Commissions',
        'pending' => '(:count pending)',
        'pending_withdrawals' => 'Pending Withdrawals',
    ],

    'members' => [
        'columns' => [
            'user' => 'User',
            'referral_code' => 'Referral Code',
            'referrals' => 'Referrals',
            'balance' => 'Balance',
            'total_earned' => 'Total Earned',
        ],
        'status' => [
            'active' => 'Active',
            'suspended' => 'Suspended',
        ],
        'actions' => [
            'suspend' => 'Suspend',
            'activate' => 'Activate',
        ],
    ],

    'commissions' => [
        'columns' => [
            'affiliate' => 'Affiliate',
            'referred_user' => 'Referred User',
            'invoice' => 'Invoice',
            'amount' => 'Amount',
            'date' => 'Date',
        ],
        'status' => [
            'approved' => 'Approved',
            'pending' => 'Pending',
            'rejected' => 'Rejected',
        ],
        'actions' => [
            'approve' => 'Approve',
            'reject' => 'Reject',
        ],
    ],

    'withdrawals' => [
        'columns' => [
            'affiliate' => 'Affiliate',
            'amount' => 'Amount',
            'method' => 'Method',
            'detail' => 'Detail',
            'requested' => 'Requested',
        ],
        'status' => [
            'approved' => 'Approved',
            'pending' => 'Pending',
            'rejected' => 'Rejected',
        ],
        'actions' => [
            'approve' => 'Approve',
            'reject' => 'Reject',
        ],
    ],

    'config_schema' => [
        'commission_type' => [
            'label'   => 'Commission Type',
            'helper'  => 'Percentage deducts a % from each paid invoice. Fixed Amount awards a flat :currency value per referral.',
            'options' => [
                'percentage' => 'Percentage (%)',
                'fixed'      => 'Fixed Amount (:currency)',
            ],
        ],
        'commission_value' => [
            'label'  => 'Commission Value',
            'helper' => 'Percentage: 0-100 (e.g. 10 = 10% of invoice). Fixed Amount: flat value in :currency (e.g. 50000).',
        ],
        'commission_trigger' => [
            'label'   => 'Commission Trigger',
            'helper'  => 'First Order Only gives a commission once per referral. Every Order gives a commission on each paid invoice.',
            'options' => [
                'first_order'  => 'First Order Only',
                'every_order'  => 'Every Order',
            ],
        ],
        'referral_scope' => [
            'label'   => 'Referral Scope',
            'helper'  => 'New Users Only tracks guests who register via referral link. All Users also tracks existing logged-in users.',
            'options' => [
                'new_users_only' => 'New Users Only',
                'all_users'      => 'All Users',
            ],
        ],
        'cookie_lifetime_days' => [
            'label'  => 'Cookie Lifetime (days)',
            'helper' => 'Duration (in days) that the referral cookie stays active in the visitor browser.',
        ],
        'min_withdrawal' => [
            'label'  => 'Minimum Withdrawal Amount',
            'helper' => 'Minimum balance in :currency required before an affiliate can submit a withdrawal.',
        ],
        'auto_approve_commission' => [
            'label'   => 'Auto Approve Commissions',
            'helper'  => 'If enabled, commissions are credited to the affiliate balance immediately after a qualifying invoice is paid.',
            'options' => [
                '0' => 'No — Require manual approval',
                '1' => 'Yes — Approve automatically',
            ],
        ],
    ],
];
