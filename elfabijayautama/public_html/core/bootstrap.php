<?php
// Global bootstrap for the application

// Composer could be used here if needed in the future

// Load config
require_once __DIR__ . '/../config/config.php';

// Start session early for CSRF/auth
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie expires when browser closes
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    
    // Set session configuration
    ini_set('session.cookie_lifetime', 0); // Session cookie expires when browser closes
    ini_set('session.gc_maxlifetime', 1800); // 30 minutes of inactivity before garbage collection
    
    session_start();
    
    // Regenerate session ID to prevent session fixation
    if (!isset($_SESSION['last_activity'])) {
        session_regenerate_id(true);
    }
    
    // Set last activity time
    $_SESSION['last_activity'] = time();
}

// Helpers and DB
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

// Security headers (basic)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Additional security headers
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Content Security Policy (allow Google Fonts and Google Maps iframe)
$csp = "default-src 'self'; "
     . "img-src 'self' data: https: blob:; "
     . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
     . "font-src 'self' https://fonts.gstatic.com; "
     . "script-src 'self'; "
     . "frame-src https://www.google.com https://maps.google.com;";
header('Content-Security-Policy: ' . $csp);

// HSTS for HTTPS only
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
