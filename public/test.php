<?php
/**
 * System Diagnostics and Extension Test
 *
 * Tests PHP extensions (pdo_mysql, gd, mbstring, json, openssl) and SMTP configuration.
 * Access this file directly in browser: /test.php
 */

declare(strict_types=1);

// Enable full error reporting for this script
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Output as HTML
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Diagnostics - Kindergarten Organizer</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; max-width: 900px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        h1 { color: #4F46E5; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; }
        h2 { color: #374151; margin-top: 30px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin: 20px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .success { color: #059669; }
        .error { color: #DC2626; }
        .warning { color: #D97706; }
        .info { color: #2563EB; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; }
        .status { font-weight: bold; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
        pre { background: #1f2937; color: #f9fafb; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 13px; }
        .btn { display: inline-block; padding: 8px 16px; background: #4F46E5; color: white; text-decoration: none; border-radius: 6px; margin: 5px 5px 5px 0; border: none; cursor: pointer; }
        .btn:hover { background: #4338CA; }
        .btn-secondary { background: #6B7280; }
        .btn-secondary:hover { background: #4B5563; }
    </style>
</head>
<body>
    <h1>System Diagnostics</h1>

    <div class="card">
        <h2>PHP Extensions Test</h2>
        <table>
            <tr>
                <th>Extension</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
            <?php
            $extensions = [
                'pdo_mysql' => [
                    'name' => 'PDO MySQL',
                    'description' => 'Required for database connectivity',
                    'test' => function() {
                        if (!extension_loaded('pdo_mysql')) {
                            return ['status' => false, 'message' => 'Extension not loaded'];
                        }
                        return ['status' => true, 'message' => 'PDO drivers: ' . implode(', ', PDO::getAvailableDrivers())];
                    }
                ],
                'gd' => [
                    'name' => 'GD Library',
                    'description' => 'Required for image processing',
                    'test' => function() {
                        if (!extension_loaded('gd')) {
                            return ['status' => false, 'message' => 'Extension not loaded'];
                        }
                        $info = gd_info();
                        $formats = [];
                        if ($info['JPEG Support'] ?? false) $formats[] = 'JPEG';
                        if ($info['PNG Support'] ?? false) $formats[] = 'PNG';
                        if ($info['GIF Read Support'] ?? false) $formats[] = 'GIF';
                        if ($info['WebP Support'] ?? false) $formats[] = 'WebP';
                        return ['status' => true, 'message' => 'Formats: ' . implode(', ', $formats) . ' | Version: ' . ($info['GD Version'] ?? 'unknown')];
                    }
                ],
                'mbstring' => [
                    'name' => 'Multibyte String',
                    'description' => 'Required for UTF-8 support',
                    'test' => function() {
                        if (!extension_loaded('mbstring')) {
                            return ['status' => false, 'message' => 'Extension not loaded'];
                        }
                        return ['status' => true, 'message' => 'Internal encoding: ' . mb_internal_encoding()];
                    }
                ],
                'json' => [
                    'name' => 'JSON',
                    'description' => 'Required for API and data handling',
                    'test' => function() {
                        if (!extension_loaded('json')) {
                            return ['status' => false, 'message' => 'Extension not loaded'];
                        }
                        $test = json_encode(['test' => 'äöü']);
                        return ['status' => true, 'message' => 'JSON encoding works correctly'];
                    }
                ],
                'openssl' => [
                    'name' => 'OpenSSL',
                    'description' => 'Required for encryption and HTTPS',
                    'test' => function() {
                        if (!extension_loaded('openssl')) {
                            return ['status' => false, 'message' => 'Extension not loaded'];
                        }
                        return ['status' => true, 'message' => 'Version: ' . OPENSSL_VERSION_TEXT];
                    }
                ],
            ];

            $allPassed = true;
            foreach ($extensions as $ext => $data) {
                $result = $data['test']();
                if (!$result['status']) $allPassed = false;
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($data['name']) ?></strong><br>
                        <small><?= htmlspecialchars($data['description']) ?></small>
                    </td>
                    <td class="status <?= $result['status'] ? 'success' : 'error' ?>">
                        <?= $result['status'] ? '✓ OK' : '✗ MISSING' ?>
                    </td>
                    <td><?= htmlspecialchars($result['message']) ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php if (!$allPassed): ?>
            <p class="error"><strong>Warning:</strong> Some required extensions are missing. Install them before using the application.</p>
        <?php else: ?>
            <p class="success"><strong>All required PHP extensions are installed.</strong></p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Database Connection Test</h2>
        <?php
        $dbConfigFile = SRC_PATH . '/config/database.php';
        if (file_exists($dbConfigFile)) {
            $dbConfig = require $dbConfigFile;

            if (empty($dbConfig['database']) || empty($dbConfig['username'])) {
                echo '<p class="warning">⚠ Database not configured. Run installation first.</p>';
            } else {
                echo '<table>';
                echo '<tr><th>Setting</th><th>Value</th></tr>';
                echo '<tr><td>Host</td><td>' . htmlspecialchars($dbConfig['host']) . '</td></tr>';
                echo '<tr><td>Port</td><td>' . htmlspecialchars((string)$dbConfig['port']) . '</td></tr>';
                echo '<tr><td>Database</td><td>' . htmlspecialchars($dbConfig['database']) . '</td></tr>';
                echo '<tr><td>Username</td><td>' . htmlspecialchars($dbConfig['username']) . '</td></tr>';
                echo '<tr><td>Charset</td><td>' . htmlspecialchars($dbConfig['charset']) . '</td></tr>';
                echo '</table>';

                // Test connection
                try {
                    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
                    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options'] ?? []);
                    echo '<p class="success">✓ Database connection successful!</p>';

                    // Test users table
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                    $userCount = $stmt->fetch()['count'];
                    echo '<p class="info">Users in database: ' . $userCount . '</p>';

                } catch (PDOException $e) {
                    echo '<p class="error">✗ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
            }
        } else {
            echo '<p class="error">✗ Database configuration file not found.</p>';
        }
        ?>
    </div>

    <div class="card">
        <h2>SMTP Test</h2>
        <?php
        $smtpConfigFile = STORAGE_PATH . '/smtp.php';
        if (file_exists($smtpConfigFile)) {
            $smtpConfig = require $smtpConfigFile;

            if (empty($smtpConfig['smtp_host'])) {
                echo '<p class="warning">⚠ SMTP not configured.</p>';
            } else {
                echo '<table>';
                echo '<tr><th>Setting</th><th>Value</th></tr>';
                echo '<tr><td>Host</td><td>' . htmlspecialchars($smtpConfig['smtp_host']) . '</td></tr>';
                echo '<tr><td>Port</td><td>' . htmlspecialchars((string)($smtpConfig['smtp_port'] ?? 587)) . '</td></tr>';
                echo '<tr><td>Encryption</td><td>' . htmlspecialchars($smtpConfig['smtp_encryption'] ?? 'tls') . '</td></tr>';
                echo '<tr><td>Username</td><td>' . htmlspecialchars($smtpConfig['smtp_user'] ?? '(not set)') . '</td></tr>';
                echo '<tr><td>From Email</td><td>' . htmlspecialchars($smtpConfig['smtp_from'] ?? '(not set)') . '</td></tr>';
                echo '</table>';

                // Test SMTP connection
                if (isset($_POST['test_smtp'])) {
                    require_once SRC_PATH . '/services/Mailer.php';
                    $mailer = new Mailer();

                    echo '<h3>SMTP Connection Test Result:</h3>';
                    if ($mailer->testConnection()) {
                        echo '<p class="success">✓ SMTP connection successful!</p>';
                    } else {
                        echo '<p class="error">✗ SMTP connection failed:</p>';
                        echo '<pre>' . htmlspecialchars(implode("\n", $mailer->getErrors())) . '</pre>';
                    }
                }

                if (isset($_POST['send_test_email']) && !empty($_POST['test_email'])) {
                    require_once SRC_PATH . '/services/Mailer.php';
                    $mailer = new Mailer();

                    echo '<h3>Send Test Email Result:</h3>';
                    if ($mailer->sendTestEmail($_POST['test_email'])) {
                        echo '<p class="success">✓ Test email sent successfully to ' . htmlspecialchars($_POST['test_email']) . '</p>';
                    } else {
                        echo '<p class="error">✗ Failed to send test email:</p>';
                        echo '<pre>' . htmlspecialchars(implode("\n", $mailer->getErrors())) . '</pre>';
                    }
                }
                ?>
                <form method="post" style="margin-top: 15px;">
                    <button type="submit" name="test_smtp" value="1" class="btn">Test SMTP Connection</button>
                </form>
                <form method="post" style="margin-top: 10px; display: flex; gap: 10px; align-items: center;">
                    <input type="email" name="test_email" placeholder="your@email.com" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; flex: 1;">
                    <button type="submit" name="send_test_email" value="1" class="btn btn-secondary">Send Test Email</button>
                </form>
                <?php
            }
        } else {
            echo '<p class="warning">⚠ SMTP configuration file not found. Configure SMTP in settings after installation.</p>';
        }
        ?>
    </div>

    <div class="card">
        <h2>Application Boot Test</h2>
        <?php
        echo '<h3>Testing Application Initialization:</h3>';
        echo '<pre>';

        try {
            // Test loading core files one by one
            $coreFiles = [
                'helpers/functions.php',
                'helpers/dates.php',
                'helpers/security.php',
                'core/Logger.php',
                'core/Database.php',
                'core/Session.php',
                'core/Validator.php',
                'core/Model.php',
                'core/Controller.php',
                'core/Router.php',
                'core/Auth.php',
                'core/App.php',
            ];

            foreach ($coreFiles as $file) {
                $path = SRC_PATH . '/' . $file;
                if (!file_exists($path)) {
                    echo "✗ MISSING: {$file}\n";
                    continue;
                }
                try {
                    require_once $path;
                    echo "✓ Loaded: {$file}\n";
                } catch (Throwable $e) {
                    echo "✗ ERROR loading {$file}: " . $e->getMessage() . "\n";
                }
            }

            echo "\n--- Testing App Initialization ---\n";

            // Check config loading
            $config = require SRC_PATH . '/config/config.php';
            echo "✓ Main config loaded\n";
            echo "  Debug mode: " . ($config['app']['debug'] ? 'ON' : 'OFF') . "\n";

            // Check for local config override
            $localConfigFile = SRC_PATH . '/config/config.local.php';
            if (file_exists($localConfigFile)) {
                $localConfig = require $localConfigFile;
                echo "✓ Local config found\n";
                if (isset($localConfig['app']['debug'])) {
                    echo "  Local debug mode: " . ($localConfig['app']['debug'] ? 'ON' : 'OFF') . "\n";
                }
            } else {
                echo "ℹ No local config file (config.local.php)\n";
            }

            echo "\n--- Testing Database Connection via App ---\n";
            $db = Database::getInstance();
            if ($db) {
                echo "✓ Database connection successful\n";

                // Check required tables
                $stmt = $db->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "  Tables found: " . count($tables) . "\n";

                // Check users table structure
                $stmt = $db->query("DESCRIBE users");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "  Users table columns: " . implode(', ', $columns) . "\n";

            } else {
                echo "✗ Database connection FAILED\n";
            }

            echo "\n--- Testing IP Ban Check (Security) ---\n";
            $testIp = getClientIp();
            echo "Your IP: {$testIp}\n";
            $banStatus = isIpBanned($testIp);
            if ($banStatus === null) {
                echo "✓ IP is NOT banned\n";
            } else {
                echo "⚠ IP is BANNED: {$banStatus}\n";
            }

        } catch (Throwable $e) {
            echo "\n✗ CRITICAL ERROR:\n";
            echo "  Message: " . $e->getMessage() . "\n";
            echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo "  Stack trace:\n" . $e->getTraceAsString() . "\n";
        }

        echo '</pre>';
        ?>
    </div>

    <div class="card">
        <h2>Login Debug Test</h2>
        <?php
        // Test login functionality
        if (isset($_POST['test_login'])) {
            $testUsername = $_POST['test_username'] ?? '';
            $testPassword = $_POST['test_password'] ?? '';

            echo '<h3>Login Test Results:</h3>';
            echo '<pre>';

            try {
                // Load required files
                require_once SRC_PATH . '/helpers/functions.php';
                require_once SRC_PATH . '/helpers/dates.php';
                require_once SRC_PATH . '/helpers/security.php';
                require_once SRC_PATH . '/core/Logger.php';
                require_once SRC_PATH . '/core/Database.php';
                require_once SRC_PATH . '/core/Session.php';
                require_once SRC_PATH . '/core/Model.php';
                require_once SRC_PATH . '/core/Auth.php';
                require_once SRC_PATH . '/models/User.php';

                echo "Step 1: Looking up user by login: " . htmlspecialchars($testUsername) . "\n";
                $user = User::findByLogin($testUsername);

                if (!$user) {
                    echo "Result: User NOT FOUND\n";
                    echo "\nChecking database for existing users...\n";

                    $dbConfig = require SRC_PATH . '/config/database.php';
                    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
                    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options'] ?? []);

                    $stmt = $pdo->query("SELECT id, username, email FROM users LIMIT 5");
                    $users = $stmt->fetchAll();
                    echo "Users in database:\n";
                    foreach ($users as $u) {
                        echo "  - ID: {$u['id']}, Username: {$u['username']}, Email: {$u['email']}\n";
                    }
                } else {
                    echo "Result: User FOUND\n";
                    echo "  ID: {$user['id']}\n";
                    echo "  Username: {$user['username']}\n";
                    echo "  Email: {$user['email']}\n";

                    echo "\nStep 2: Verifying password...\n";
                    $passwordValid = password_verify($testPassword, $user['password_hash']);
                    echo "Result: Password " . ($passwordValid ? "VALID" : "INVALID") . "\n";

                    if (!$passwordValid) {
                        echo "\nPassword hash details:\n";
                        echo "  Hash algorithm: " . password_get_info($user['password_hash'])['algoName'] . "\n";
                        echo "  Hash length: " . strlen($user['password_hash']) . " chars\n";
                    }

                    echo "\nStep 3: Testing session functionality...\n";
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                        echo "Session started successfully. ID: " . session_id() . "\n";
                    } else {
                        echo "Session already active. ID: " . session_id() . "\n";
                    }

                    echo "\nStep 4: Testing Auth::attempt()...\n";
                    $attemptResult = Auth::attempt($testUsername, $testPassword, false);
                    echo "Auth::attempt() result: " . ($attemptResult ? "SUCCESS" : "FAILED") . "\n";
                }

            } catch (Exception $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
                echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
                echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
            } catch (Error $e) {
                echo "FATAL ERROR: " . $e->getMessage() . "\n";
                echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
                echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
            }

            echo '</pre>';
        }
        ?>
        <form method="post" style="margin-top: 15px;">
            <div style="margin-bottom: 10px;">
                <input type="text" name="test_username" placeholder="Username or Email" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; width: 100%; max-width: 300px;">
            </div>
            <div style="margin-bottom: 10px;">
                <input type="password" name="test_password" placeholder="Password" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; width: 100%; max-width: 300px;">
            </div>
            <button type="submit" name="test_login" value="1" class="btn">Test Login</button>
        </form>
    </div>

    <div class="card">
        <h2>PHP Configuration</h2>
        <?php
        // Helper to convert shorthand to bytes
        function convertToBytes(string $value): int {
            $value = trim($value);
            $last = strtolower($value[strlen($value)-1]);
            $numValue = (int)$value;
            switch($last) {
                case 'g': $numValue *= 1024;
                case 'm': $numValue *= 1024;
                case 'k': $numValue *= 1024;
            }
            return $numValue;
        }

        // Helper to check if value meets minimum
        function checkMinimum(string $current, string $minimum): array {
            $currentBytes = convertToBytes($current);
            $minimumBytes = convertToBytes($minimum);
            return [
                'ok' => $currentBytes >= $minimumBytes,
                'current' => $current,
                'minimum' => $minimum
            ];
        }

        // PHP settings with requirements
        $phpSettings = [
            'PHP Version' => [
                'value' => phpversion(),
                'check' => version_compare(phpversion(), '8.1.0', '>='),
                'required' => '>= 8.1.0'
            ],
            'memory_limit' => [
                'value' => ini_get('memory_limit'),
                'check' => convertToBytes(ini_get('memory_limit')) >= convertToBytes('128M') || ini_get('memory_limit') == -1,
                'required' => '>= 128M'
            ],
            'max_execution_time' => [
                'value' => ini_get('max_execution_time') . 's',
                'check' => ini_get('max_execution_time') >= 30 || ini_get('max_execution_time') == 0,
                'required' => '>= 30s (0 = unlimited)'
            ],
            'max_input_time' => [
                'value' => ini_get('max_input_time') . 's',
                'check' => ini_get('max_input_time') >= 60 || ini_get('max_input_time') == -1,
                'required' => '>= 60s'
            ],
            'upload_max_filesize' => [
                'value' => ini_get('upload_max_filesize'),
                'check' => convertToBytes(ini_get('upload_max_filesize')) >= convertToBytes('10M'),
                'required' => '>= 10M'
            ],
            'post_max_size' => [
                'value' => ini_get('post_max_size'),
                'check' => convertToBytes(ini_get('post_max_size')) >= convertToBytes('12M'),
                'required' => '>= 12M (should be > upload_max_filesize)'
            ],
            'max_file_uploads' => [
                'value' => ini_get('max_file_uploads'),
                'check' => ini_get('max_file_uploads') >= 10,
                'required' => '>= 10'
            ],
            'display_errors' => [
                'value' => ini_get('display_errors') ? 'On' : 'Off',
                'check' => null, // Info only
                'required' => 'Off in production, On for debugging'
            ],
            'error_reporting' => [
                'value' => error_reporting() . ' (E_ALL = ' . E_ALL . ')',
                'check' => null, // Info only
                'required' => 'E_ALL recommended for debugging'
            ],
            'log_errors' => [
                'value' => ini_get('log_errors') ? 'On' : 'Off',
                'check' => (bool)ini_get('log_errors'),
                'required' => 'On'
            ],
            'error_log' => [
                'value' => ini_get('error_log') ?: '(default)',
                'check' => null, // Info only
                'required' => 'Path to error log'
            ],
        ];
        ?>
        <h3>Core PHP Settings</h3>
        <table>
            <tr><th>Setting</th><th>Current Value</th><th>Status</th><th>Required</th></tr>
            <?php foreach ($phpSettings as $name => $setting): ?>
            <tr>
                <td><code><?= htmlspecialchars($name) ?></code></td>
                <td><?= htmlspecialchars($setting['value']) ?></td>
                <td class="<?= $setting['check'] === null ? 'info' : ($setting['check'] ? 'success' : 'error') ?>">
                    <?= $setting['check'] === null ? 'ℹ️' : ($setting['check'] ? '✓' : '✗') ?>
                </td>
                <td><small><?= htmlspecialchars($setting['required']) ?></small></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php
        // Session settings
        $sessionSettings = [
            'session.save_handler' => ini_get('session.save_handler'),
            'session.save_path' => ini_get('session.save_path') ?: '(default)',
            'session.name' => ini_get('session.name'),
            'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime') . 's',
            'session.cookie_lifetime' => ini_get('session.cookie_lifetime') . 's',
            'session.cookie_httponly' => ini_get('session.cookie_httponly') ? 'On' : 'Off',
            'session.cookie_secure' => ini_get('session.cookie_secure') ? 'On' : 'Off',
            'session.cookie_samesite' => ini_get('session.cookie_samesite') ?: '(not set)',
            'session.use_strict_mode' => ini_get('session.use_strict_mode') ? 'On' : 'Off',
            'session.use_only_cookies' => ini_get('session.use_only_cookies') ? 'On' : 'Off',
        ];
        ?>
        <h3>Session Settings</h3>
        <table>
            <tr><th>Setting</th><th>Value</th></tr>
            <?php foreach ($sessionSettings as $name => $value): ?>
            <tr>
                <td><code><?= htmlspecialchars($name) ?></code></td>
                <td><?= htmlspecialchars($value) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php
        // Test session functionality
        echo '<h3>Session Functionality Test</h3>';
        $sessionPath = session_save_path() ?: sys_get_temp_dir();
        $sessionWritable = is_writable($sessionPath);
        ?>
        <table>
            <tr><th>Test</th><th>Result</th></tr>
            <tr>
                <td>Session save path writable</td>
                <td class="<?= $sessionWritable ? 'success' : 'error' ?>">
                    <?= $sessionWritable ? '✓ Writable' : '✗ NOT Writable' ?>
                    (<?= htmlspecialchars($sessionPath) ?>)
                </td>
            </tr>
            <?php
            // Test starting a session
            $sessionTestOk = false;
            $sessionError = '';
            try {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['test_value'] = 'test_' . time();
                $sessionTestOk = isset($_SESSION['test_value']);
            } catch (Throwable $e) {
                $sessionError = $e->getMessage();
            }
            ?>
            <tr>
                <td>Session start and write</td>
                <td class="<?= $sessionTestOk ? 'success' : 'error' ?>">
                    <?= $sessionTestOk ? '✓ Working' : '✗ FAILED: ' . htmlspecialchars($sessionError) ?>
                </td>
            </tr>
        </table>

        <?php
        // Database-specific PHP settings
        $dbSettings = [
            'PDO drivers' => implode(', ', PDO::getAvailableDrivers()),
            'default_socket_timeout' => ini_get('default_socket_timeout') . 's',
            'mysqli.default_socket' => ini_get('mysqli.default_socket') ?: '(default)',
            'pdo_mysql.default_socket' => ini_get('pdo_mysql.default_socket') ?: '(default)',
        ];
        ?>
        <h3>Database-Related Settings</h3>
        <table>
            <tr><th>Setting</th><th>Value</th></tr>
            <?php foreach ($dbSettings as $name => $value): ?>
            <tr>
                <td><code><?= htmlspecialchars($name) ?></code></td>
                <td><?= htmlspecialchars($value) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php
        // Security-related settings
        $securitySettings = [
            'allow_url_fopen' => ini_get('allow_url_fopen') ? 'On' : 'Off',
            'allow_url_include' => ini_get('allow_url_include') ? 'On (DANGEROUS!)' : 'Off',
            'open_basedir' => ini_get('open_basedir') ?: '(not set)',
            'disable_functions' => ini_get('disable_functions') ?: '(none)',
            'expose_php' => ini_get('expose_php') ? 'On' : 'Off',
        ];
        ?>
        <h3>Security Settings</h3>
        <table>
            <tr><th>Setting</th><th>Value</th></tr>
            <?php foreach ($securitySettings as $name => $value): ?>
            <tr>
                <td><code><?= htmlspecialchars($name) ?></code></td>
                <td class="<?= ($name === 'allow_url_include' && ini_get('allow_url_include')) ? 'error' : '' ?>">
                    <?= htmlspecialchars(strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>Application Logs</h2>
        <?php
        $logDir = STORAGE_PATH . '/logs';
        if (is_dir($logDir)) {
            $logFiles = glob($logDir . '/app-*.log');
            rsort($logFiles); // Most recent first

            if (empty($logFiles)) {
                echo '<p class="warning">No log files found.</p>';
            } else {
                $latestLog = $logFiles[0];
                echo '<p class="info">Latest log file: <code>' . basename($latestLog) . '</code></p>';

                // Show last 30 lines
                $lines = file($latestLog);
                $lastLines = array_slice($lines, -30);

                if (empty($lastLines)) {
                    echo '<p>Log file is empty.</p>';
                } else {
                    echo '<pre style="max-height: 400px; overflow-y: auto;">';
                    foreach ($lastLines as $line) {
                        echo htmlspecialchars($line);
                    }
                    echo '</pre>';
                }
            }
        } else {
            echo '<p class="warning">Log directory does not exist: ' . htmlspecialchars($logDir) . '</p>';
        }
        ?>
    </div>

    <div class="card">
        <h2>File Permissions</h2>
        <table>
            <tr><th>Path</th><th>Exists</th><th>Writable</th></tr>
            <?php
            $paths = [
                STORAGE_PATH => 'storage/',
                STORAGE_PATH . '/logs' => 'storage/logs/',
                STORAGE_PATH . '/cache' => 'storage/cache/',
                PUBLIC_PATH . '/uploads' => 'public/uploads/',
                ROOT_PATH . '/temp' => 'temp/',
            ];

            foreach ($paths as $path => $label) {
                $exists = file_exists($path);
                $writable = is_writable($path);
                ?>
                <tr>
                    <td><code><?= htmlspecialchars($label) ?></code></td>
                    <td class="<?= $exists ? 'success' : 'error' ?>"><?= $exists ? '✓' : '✗' ?></td>
                    <td class="<?= $writable ? 'success' : 'error' ?>"><?= $writable ? '✓' : '✗' ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>

    <p style="text-align: center; margin-top: 30px; color: #6b7280;">
        <a href="/" class="btn btn-secondary">← Back to Application</a>
    </p>

</body>
</html>
