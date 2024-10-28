<?php 

return [
    'name' => 'UserManagement',
    
    'auth' => [
        // Authentication Settings
        'token_expiration' => env('AUTH_TOKEN_EXPIRATION', 60), // minutes
        'refresh_token_expiration' => env('AUTH_REFRESH_TOKEN_EXPIRATION', 43200), // 30 days
        'max_login_attempts' => env('AUTH_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('AUTH_LOCKOUT_DURATION', 300), // 5 minutes
        'password_history' => env('AUTH_PASSWORD_HISTORY', 3), // Remember last 3 passwords
        'require_password_change' => env('AUTH_REQUIRE_PASSWORD_CHANGE', 90), // days
        
        // Email Verification
        'verify_email' => env('AUTH_VERIFY_EMAIL', true),
        'verification_token_expiration' => env('AUTH_VERIFICATION_TOKEN_EXPIRATION', 1440), // 24 hours
        
        // Password Reset
        'reset_token_expiration' => env('AUTH_RESET_TOKEN_EXPIRATION', 60), // minutes
        'password_rules' => [
            'min_length' => env('AUTH_PASSWORD_MIN_LENGTH', 8),
            'require_uppercase' => env('AUTH_PASSWORD_REQUIRE_UPPERCASE', true),
            'require_numeric' => env('AUTH_PASSWORD_REQUIRE_NUMERIC', true),
            'require_special' => env('AUTH_PASSWORD_REQUIRE_SPECIAL', true),
            'prevent_common' => env('AUTH_PASSWORD_PREVENT_COMMON', true),
        ],
    ],
    
    'security' => [
        // Device Management
        'max_devices' => env('SECURITY_MAX_DEVICES', 5),
        'track_devices' => env('SECURITY_TRACK_DEVICES', true),
        'trusted_device_duration' => env('SECURITY_TRUSTED_DEVICE_DURATION', 30), // days
        'require_2fa_for_untrusted' => env('SECURITY_REQUIRE_2FA_UNTRUSTED', false),
        
        // Session Management
        'session_lifetime' => env('SECURITY_SESSION_LIFETIME', 120), // minutes
        'session_rotate_interval' => env('SECURITY_SESSION_ROTATE_INTERVAL', 30), // minutes
        
        // Rate Limiting
        'throttle' => [
            'login' => [
                'max_attempts' => env('SECURITY_LOGIN_MAX_ATTEMPTS', 5),
                'decay_minutes' => env('SECURITY_LOGIN_DECAY_MINUTES', 1),
            ],
            'register' => [
                'max_attempts' => env('SECURITY_REGISTER_MAX_ATTEMPTS', 3),
                'decay_minutes' => env('SECURITY_REGISTER_DECAY_MINUTES', 60),
            ],
            'password_reset' => [
                'max_attempts' => env('SECURITY_PASSWORD_RESET_MAX_ATTEMPTS', 3),
                'decay_minutes' => env('SECURITY_PASSWORD_RESET_DECAY_MINUTES', 60),
            ],
        ],
    ],
    
    'profile' => [
        // Avatar Settings
        'avatar' => [
            'max_size' => env('PROFILE_AVATAR_MAX_SIZE', 2048), // KB
            'allowed_types' => ['jpeg', 'png', 'jpg'],
            'min_dimensions' => [
                'width' => env('PROFILE_AVATAR_MIN_WIDTH', 100),
                'height' => env('PROFILE_AVATAR_MIN_HEIGHT', 100),
            ],
            'max_dimensions' => [
                'width' => env('PROFILE_AVATAR_MAX_WIDTH', 2000),
                'height' => env('PROFILE_AVATAR_MAX_HEIGHT', 2000),
            ],
        ],
        
        // Meta Data Settings
        'meta_data' => [
            'allowed_social_platforms' => [
                'twitter',
                'facebook',
                'linkedin',
                'instagram',
                'github',
            ],
            'max_bio_length' => env('PROFILE_MAX_BIO_LENGTH', 500),
        ],
    ],
    
    'notifications' => [
        // Email Notifications
        'mail' => [
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            'from_name' => env('MAIL_FROM_NAME', 'User Management System'),
        ],
        
        // SMS Notifications (if implemented)
        'sms' => [
            'enabled' => env('SMS_NOTIFICATIONS_ENABLED', false),
            'provider' => env('SMS_PROVIDER', 'twilio'),
        ],
        
        // Push Notifications (if implemented)
        'push' => [
            'enabled' => env('PUSH_NOTIFICATIONS_ENABLED', false),
            'provider' => env('PUSH_PROVIDER', 'firebase'),
        ],
    ],
];
