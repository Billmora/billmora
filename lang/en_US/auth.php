<?php

return [
    'page' => [
        'login' => 'Sign In to your Account',
        'register' => 'Sign Up your Account',
        'forgot_password' => 'Request Reset Password',
        'reset_password' => 'Update your Password',
    ],
    
    'remembered_password' => 'Already remembered your password?',
    'forgot_password' => 'Forgot Password?',
    'have_account' => 'Already have an account?',
    'dont_have_account' => 'Don\'t have an account?',

    'invalid_credentials' => 'No account matching those credentials could be found.',
    'registration_successful' => 'Thank you for registering. We have sent you an email for verification. Please check your inbox and follow the instructions to verify your email.',

    'email' => [
        'not_verified' => 'Your email has not been verified. Please check your inbox or resend the verification email.',
        'has_verified' => 'Your email has been successfully verified. You can login now.',
        'already_verified' => 'Your email is already verified. Please login.',
        'invalid_token' => 'Invalid email verification token.',
        'expired_token' => 'Your verification token has expired. Please request a new one.',
        'already_requested' => 'You have already requested a verification email. Please check your inbox.',
        'resent' => 'We have resent a verification email to you. Please check your inbox.',
        'invalid_request' => 'Invalid request token for email verification.',
    ],

    'password' => [
        'reset_request_sent' => 'We have sent you an email to reset your password. Please check your inbox.',
        'already_requested' => 'You have already requested a password reset. Please check your inbox.',
        'invalid_request' => 'Invalid password reset request.',
        'expired_request' => 'Your password reset request has expired. Please request again.',
        'email_not_found' => 'The email address does not match our records. Please try again.',
        'reset_success' => 'Your password has been successfully reset. You can login now.',
        'current_mismatch' => 'The provided password does not match your current password.',
    ],

    '2fa' => [
        'title' => 'Two-Factor Authentication',
        'description' => 'Two-Factor Authentication (2FA) adds an extra layer of security to your account by requiring a second form of verification in addition to your password.',

        'setup' => [
            'title' => 'Set Up Two-Factor Authentication',
            'description' => 'Secure your account by enabling Two-Factor Authentication (2FA). Follow the steps below to set up 2FA using an authentication app.',
            'step_1' => '1. Scan QR code using an authenticator app or enter the key manually',
            'step_2' => '2. Enter the 6 digit code from your authenticator app',
            'step_3' => '3. Continue to the next step',
            'has_setup' => 'You have setup Two-Factor Authentication on your account.',
            'not_setup' => 'You have not setup Two-Factor Authentication on your account.',
        ],

        'backup' => [
            'title' => 'Backup Codes Two-Factor Authentication',
            'description' => 'Backup codes can be used to access your account if you lose access to your authentication device. Store them in a safe place.',
            'not_downloaded' => 'You have not downloaded your backup codes. Please download and store them in a safe place.',
            'recovery_code' => 'Recovery Code from the Backup Codes',
        ],

        'verify' => [
            'title' => 'Verify Two-Factor Authentication',
            'description' => 'Enter the verification code from your authentication app to complete the Two-Factor Authentication.',
            'totp' => 'TOTP Code from the app',
            'invalid_totp' => 'The entered TOTP Code is invalid.',
        ],

        'recovery' => [
            'title' => 'Recover Two-Factor Authentication',
            'description' => 'If you have lost access to your authenticator app, use your backup codes or contact support to regain access to your account.',
            'lost_access' => 'Lost access to Authenticator App?',
            'have_access' => 'Have access to Authenticator App?',
        ],
    ],
];
