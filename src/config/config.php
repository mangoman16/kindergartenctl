<?php
/**
 * Main Configuration File
 */

return [
    // Application settings
    'app' => [
        'name' => 'Kindergarten Spiele Organizer',
        'version' => '1.0.0',
        'timezone' => 'Europe/Vienna',
        'locale' => 'de_AT.UTF-8',
        'charset' => 'UTF-8',
        'debug' => false, // Set to true only for development
    ],

    // Session settings
    'session' => [
        'name' => '__app_sess',
        'lifetime' => 86400, // 24 hours in seconds
        'remember_lifetime' => 2592000, // 30 days in seconds
        'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    // Security settings
    'security' => [
        'csrf_token_lifetime' => 3600, // 1 hour
        'password_min_length' => 8,
        'ip_ban_threshold' => 5, // Failed attempts before temporary ban
        'ip_ban_permanent_threshold' => 10, // Failed attempts before permanent ban
        'ip_ban_duration' => 900, // 15 minutes in seconds
        'password_reset_lifetime' => 3600, // 1 hour
    ],

    // Upload settings
    'upload' => [
        'max_size' => 10485760, // 10 MB in bytes
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'thumb_width' => 150,
        'thumb_height' => 150,
        'full_width' => 600,
        'full_height' => 600,
        'quality' => 85,
    ],

    // Pagination
    'pagination' => [
        'items_per_page' => 24,
    ],
];
