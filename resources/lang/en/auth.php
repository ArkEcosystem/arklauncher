<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed'                            => 'These credentials do not match our records.',
    'throttle'                          => 'Too many login attempts. Please try again in :seconds seconds.',

    'emergency_token_login_page_header' => 'Login Via Emergency Token',
    'emergency_token_invalid'           => 'The emergency token was invalid.',

    'passwords' => [
        'email' => [
            'page_header' => 'Reset Password',

        ],
        'reset' => [
            'title'            => 'Reset Password',
            'description'      => 'Enter your email address below to receive instructions for resetting your password.',
            'reset_link_email' => 'Request submitted. If your email address is associated with a :appName account, you will receive an email with instructions on how to reset your password.',
        ],
    ],

    'token' => [
        'page_header'       => 'Two-Factor Authentication',
        'device_lost'       => 'Lost Your Device?',
        'token'             => '2FA Token',
        'reset_code'        => 'Reset Code',
        'reset_description' => 'Fill in the reset code that was shown when you enabled Two-Factor Authentication for your account in order to disable it.',
    ],

    'verify' => [
        'page_header'         => 'Verify Your Email Address',
        'link_description'    => 'A verification link has been sent to your email address.',
        'resend_verification' => 'Before proceeding, please check your email for a verification link. If you did not receive the email, <a href=":href" class="link">click here to request another</a>.',
    ],

    'verified' => [
        'page_header'      => 'Congratulations!',
        'page_description' => 'Your email address has been verified.',
        'cta'              => 'Go to dashboard',
    ],
];
