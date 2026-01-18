#!/usr/bin/env php
<?php
/**
 * Test Runner Script
 * Runs comprehensive tests and logs all output
 */

// Define paths
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load Logger
require_once SRC_PATH . '/core/Logger.php';

echo "=== Kindergarten Organizer Test Suite ===\n\n";

// Log test start
Logger::info('Test suite started', ['script' => 'test-runner.php']);

// Check PHP version
echo "1. Checking PHP version...\n";
$phpVersion = PHP_VERSION;
echo "   PHP Version: {$phpVersion}\n";
if (version_compare($phpVersion, '8.0.0', '<')) {
    echo "   ❌ FAIL: PHP 8.0+ required\n";
    Logger::error('PHP version check failed', ['version' => $phpVersion]);
    exit(1);
} else {
    echo "   ✓ PASS: PHP version is compatible\n";
    Logger::info('PHP version check passed', ['version' => $phpVersion]);
}
echo "\n";

// Check required extensions
echo "2. Checking required PHP extensions...\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ {$ext}\n";
    } else {
        echo "   ❌ {$ext} (MISSING)\n";
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "\n   ❌ FAIL: Missing extensions: " . implode(', ', $missingExtensions) . "\n";
    Logger::error('Required extensions missing', ['extensions' => $missingExtensions]);
    exit(1);
} else {
    echo "   ✓ PASS: All required extensions loaded\n";
    Logger::info('All required extensions loaded');
}
echo "\n";

// Check directory permissions
echo "3. Checking directory permissions...\n";
$directories = [
    'storage' => STORAGE_PATH,
    'storage/logs' => STORAGE_PATH . '/logs',
    'storage/cache' => STORAGE_PATH . '/cache',
    'temp' => ROOT_PATH . '/temp',
    'public/uploads' => ROOT_PATH . '/public/uploads'
];

$permissionIssues = [];
foreach ($directories as $name => $path) {
    if (!is_dir($path)) {
        echo "   ⚠ {$name}: Directory does not exist, attempting to create...\n";
        if (@mkdir($path, 0755, true)) {
            echo "   ✓ {$name}: Created successfully\n";
            Logger::info('Directory created', ['path' => $path]);
        } else {
            echo "   ❌ {$name}: Failed to create\n";
            $permissionIssues[] = $name;
            Logger::error('Failed to create directory', ['path' => $path]);
        }
    } elseif (!is_writable($path)) {
        echo "   ❌ {$name}: Not writable\n";
        $permissionIssues[] = $name;
        Logger::warning('Directory not writable', ['path' => $path]);
    } else {
        echo "   ✓ {$name}: Writable\n";
    }
}

if (!empty($permissionIssues)) {
    echo "\n   ⚠ WARNING: Some directories have permission issues\n";
} else {
    echo "   ✓ PASS: All directories have correct permissions\n";
}
echo "\n";

// Run PHPUnit tests if available
echo "4. Running PHPUnit tests...\n";
if (file_exists(ROOT_PATH . '/vendor/bin/phpunit')) {
    $phpunitCmd = ROOT_PATH . '/vendor/bin/phpunit --testdox';
    echo "   Executing: {$phpunitCmd}\n";
    Logger::info('Running PHPUnit tests');

    passthru($phpunitCmd, $exitCode);

    if ($exitCode === 0) {
        echo "\n   ✓ PASS: All PHPUnit tests passed\n";
        Logger::info('All PHPUnit tests passed');
    } else {
        echo "\n   ❌ FAIL: Some PHPUnit tests failed (exit code: {$exitCode})\n";
        Logger::error('PHPUnit tests failed', ['exit_code' => $exitCode]);
    }
} else {
    echo "   ⚠ SKIP: PHPUnit not installed (run 'composer install' to install)\n";
    echo "   Note: You can install PHPUnit with: composer require --dev phpunit/phpunit\n";
    Logger::warning('PHPUnit not available');
}
echo "\n";

// Test Logger functionality
echo "5. Testing Logger functionality...\n";
try {
    Logger::info('Logger test - info level');
    Logger::warning('Logger test - warning level');
    Logger::error('Logger test - error level');
    Logger::debug('Logger test - debug level');

    // Check if log file was created
    $logFile = STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log';
    if (file_exists($logFile)) {
        echo "   ✓ Log file created: {$logFile}\n";
        $logContent = file_get_contents($logFile);
        $logLines = substr_count($logContent, "\n");
        echo "   ✓ Log entries written: {$logLines} lines\n";
        echo "   ✓ PASS: Logger is working correctly\n";
        Logger::info('Logger functionality test passed');
    } else {
        echo "   ❌ FAIL: Log file not created\n";
        Logger::error('Logger test failed - no log file created');
    }
} catch (Exception $e) {
    echo "   ❌ FAIL: Logger error - " . $e->getMessage() . "\n";
    Logger::error('Logger test exception', ['error' => $e->getMessage()]);
}
echo "\n";

// Test configuration loading
echo "6. Testing configuration system...\n";
try {
    require_once SRC_PATH . '/core/App.php';

    // Test if App class exists
    if (class_exists('App')) {
        echo "   ✓ App class loaded successfully\n";

        // Test config method
        $timezone = App::config('app.timezone', 'UTC');
        echo "   ✓ Config method works (timezone: {$timezone})\n";

        echo "   ✓ PASS: Configuration system working\n";
        Logger::info('Configuration system test passed');
    } else {
        echo "   ❌ FAIL: App class not found\n";
        Logger::error('App class not found');
    }
} catch (Exception $e) {
    echo "   ❌ FAIL: Configuration error - " . $e->getMessage() . "\n";
    Logger::error('Configuration test exception', ['error' => $e->getMessage()]);
}
echo "\n";

// Summary
echo "=== Test Suite Summary ===\n";
echo "Check the log file for detailed information: " . STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log' . "\n";
Logger::info('Test suite completed');

// Check if we should view recent logs
if (isset($argv[1]) && $argv[1] === '--show-logs') {
    echo "\n=== Recent Log Entries ===\n";
    $recentLogs = Logger::getRecentLogs(20);
    foreach ($recentLogs as $log) {
        echo $log . "\n";
    }
}

echo "\n✓ Testing complete!\n";
