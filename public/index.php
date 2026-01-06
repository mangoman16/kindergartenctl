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

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

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
    // Log error
    error_log($e->getMessage());

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
