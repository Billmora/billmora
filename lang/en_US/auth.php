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
];
