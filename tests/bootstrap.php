<?php
/**
 * PHPUnit Bootstrap File
 */

// Define paths for testing
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('TEMP_PATH', ROOT_PATH . '/temp');

// Load helpers
require_once SRC_PATH . '/helpers/functions.php';
require_once SRC_PATH . '/helpers/security.php';
if (file_exists(SRC_PATH . '/helpers/dates.php')) {
    require_once SRC_PATH . '/helpers/dates.php';
}

// Load core classes
require_once SRC_PATH . '/core/Logger.php';
require_once SRC_PATH . '/core/Database.php';
require_once SRC_PATH . '/core/Model.php';
require_once SRC_PATH . '/core/Session.php';
require_once SRC_PATH . '/core/Validator.php';

// Mock App class for testing
class App
{
    private static array $config = [];

    public static function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }
}

// Set default test configuration
App::setConfig([
    'session' => [
        'name' => 'test_session',
        'lifetime' => 3600,
    ],
    'security' => [
        'csrf_token_lifetime' => 3600,
        'ip_ban_threshold' => 5,
        'ip_ban_permanent_threshold' => 10,
        'ip_ban_duration' => 900,
    ],
]);
