<?php
/**
 * Kindergarten Game Organizer
 * Main Entry Point
 */

declare(strict_types=1);

// Define base paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('TEMP_PATH', ROOT_PATH . '/temp');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Error reporting - display_errors controlled after config loads
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Generate CSP nonce for inline scripts/styles
define('CSP_NONCE', base64_encode(random_bytes(16)));

// Set CSP header with nonce (replaces static .htaccess CSP)
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-" . CSP_NONCE . "' https://cdn.jsdelivr.net; style-src 'self' 'nonce-" . CSP_NONCE . "' https://cdn.jsdelivr.net; img-src 'self' data: blob:; font-src 'self'; connect-src 'self'; frame-ancestors 'self';");

// Start output buffering
ob_start();

// Load composer autoload if exists
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php';
}

// Load helper functions
require_once SRC_PATH . '/helpers/functions.php';
require_once SRC_PATH . '/helpers/dates.php';
require_once SRC_PATH . '/helpers/security.php';

// Load core classes
require_once SRC_PATH . '/core/App.php';

// Check if installed - redirect to installer if not
$installedLockFile = ROOT_PATH . '/installed.lock';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$isInstallRoute = strpos($requestUri, '/install') === 0;

if (!file_exists($installedLockFile) && !$isInstallRoute) {
    header('Location: /install');
    exit;
}

// If already installed, prevent access to installer
if (file_exists($installedLockFile) && $isInstallRoute) {
    header('Location: /');
    exit;
}

// Initialize and run application
try {
    $app = new App();
    $app->run();
} catch (Exception $e) {
    // Log error using Logger if available, fallback to error_log
    if (class_exists('Logger')) {
        Logger::exception($e, [
            'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'
        ]);
    } else {
        error_log($e->getMessage());
    }

    // Show error page in development, generic message in production
    if (ini_get('display_errors')) {
        echo '<h1>Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>Ein Fehler ist aufgetreten</h1>';
        echo '<p>Bitte versuchen Sie es später erneut.</p>';
    }
}

ob_end_flush();
