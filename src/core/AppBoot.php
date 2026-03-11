<?php
declare(strict_types=1);

/**
 * AppBoot - Shared bootstrap for web and CLI entry points.
 *
 * Loads configuration, sets up the environment, and ensures all core classes,
 * models, and services are available.  Does NOT start a session, create a
 * router, or emit HTTP headers — those are web-only concerns handled by App.
 */
class AppBoot
{
    private static array $config = [];
    private static bool $booted = false;

    /**
     * Run the shared bootstrap sequence.
     *
     * Safe to call more than once (idempotent).
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::loadCoreClasses();
        self::loadConfig();
        self::setupEnvironment();
        self::loadModels();
        self::loadServices();

        self::$booted = true;
    }

    // ------------------------------------------------------------------
    // Class loading
    // ------------------------------------------------------------------

    private static function loadCoreClasses(): void
    {
        $coreFiles = [
            'Logger',
            'Database',
            'Model',
            'Validator',
            'ServiceResult',
        ];

        foreach ($coreFiles as $file) {
            require_once SRC_PATH . '/core/' . $file . '.php';
        }
    }

    private static function loadModels(): void
    {
        $modelFiles = [
            'Game', 'Material', 'Box', 'Category', 'Tag',
            'Group', 'Location', 'User', 'CalendarEvent', 'PasswordReset',
        ];

        foreach ($modelFiles as $file) {
            $path = SRC_PATH . '/models/' . $file . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    private static function loadServices(): void
    {
        $serviceFiles = [
            'ChangelogService', 'TransactionService', 'ImageProcessor', 'Mailer',
            'GameService', 'MaterialService', 'BoxService', 'CategoryService',
            'TagService', 'GroupService', 'LocationService', 'CalendarService',
            'SearchService', 'UserService', 'SettingsService', 'FavoriteService',
        ];

        foreach ($serviceFiles as $file) {
            $path = SRC_PATH . '/services/' . $file . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    // ------------------------------------------------------------------
    // Configuration
    // ------------------------------------------------------------------

    private static function loadConfig(): void
    {
        $configFile = SRC_PATH . '/config/config.php';
        if (file_exists($configFile)) {
            self::$config = require $configFile;
        }

        $localConfigFile = SRC_PATH . '/config/config.local.php';
        if (file_exists($localConfigFile)) {
            $localConfig = require $localConfigFile;
            self::$config = self::mergeConfigRecursive(self::$config, $localConfig);
        }
    }

    private static function mergeConfigRecursive(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (isset($base[$key]) && is_array($base[$key]) && is_array($value)) {
                $base[$key] = self::mergeConfigRecursive($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }
        return $base;
    }

    // ------------------------------------------------------------------
    // Environment
    // ------------------------------------------------------------------

    private static function setupEnvironment(): void
    {
        $timezone = self::$config['app']['timezone'] ?? 'Europe/Vienna';
        date_default_timezone_set($timezone);

        $locale = self::$config['app']['locale'] ?? 'de_AT.UTF-8';
        setlocale(LC_ALL, $locale);

        mb_internal_encoding(self::$config['app']['charset'] ?? 'UTF-8');

        $debug = self::$config['app']['debug'] ?? false;
        if (file_exists(ROOT_PATH . '/storage/debug.flag')) {
            $debug = true;
        }
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    // ------------------------------------------------------------------
    // Public accessors (used by both App and CLI)
    // ------------------------------------------------------------------

    /**
     * Get a configuration value using dot notation.
     */
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

    /**
     * Check if the application has been installed.
     */
    public static function isInstalled(): bool
    {
        return file_exists(ROOT_PATH . '/installed.lock');
    }

    /**
     * Get the base URL (web context only — falls back to config or localhost).
     */
    public static function baseUrl(): string
    {
        $configured = trim(self::$config['app']['url'] ?? '');
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        if (PHP_SAPI === 'cli') {
            return 'http://localhost';
        }

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
}
