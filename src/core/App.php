<?php
/**
 * Application Bootstrap Class
 */

class App
{
    private static array $config = [];
    private Router $router;

    /**
     * Initialize the application
     */
    public function __construct()
    {
        $this->loadCoreClasses();
        $this->loadConfig();
        $this->setupEnvironment();
        $this->router = new Router();
        $this->router->loadRoutes();
    }

    /**
     * Load core classes
     */
    private function loadCoreClasses(): void
    {
        require_once SRC_PATH . '/core/Logger.php';
        require_once SRC_PATH . '/core/Database.php';
        require_once SRC_PATH . '/core/Router.php';
        require_once SRC_PATH . '/core/Session.php';
        require_once SRC_PATH . '/core/Controller.php';
        require_once SRC_PATH . '/core/Model.php';
        require_once SRC_PATH . '/core/Auth.php';
        require_once SRC_PATH . '/core/Validator.php';

        // Load ChangelogService if exists
        $changelogService = SRC_PATH . '/core/ChangelogService.php';
        if (file_exists($changelogService)) {
            require_once $changelogService;
        }

        // Load ImageProcessor if exists
        $imageProcessor = SRC_PATH . '/core/ImageProcessor.php';
        if (file_exists($imageProcessor)) {
            require_once $imageProcessor;
        }

        // Load Mailer if exists
        $mailer = SRC_PATH . '/core/Mailer.php';
        if (file_exists($mailer)) {
            require_once $mailer;
        }
    }

    /**
     * Load configuration
     */
    private function loadConfig(): void
    {
        $configFile = SRC_PATH . '/config/config.php';
        if (file_exists($configFile)) {
            self::$config = require $configFile;
        }
    }

    /**
     * Setup environment settings
     */
    private function setupEnvironment(): void
    {
        // Timezone
        $timezone = self::$config['app']['timezone'] ?? 'Europe/Vienna';
        date_default_timezone_set($timezone);

        // Locale
        $locale = self::$config['app']['locale'] ?? 'de_AT.UTF-8';
        setlocale(LC_ALL, $locale);

        // Charset
        mb_internal_encoding(self::$config['app']['charset'] ?? 'UTF-8');

        // Error reporting based on debug mode
        if (self::$config['app']['debug'] ?? false) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        // Start session if not in install mode
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $isInstallRoute = strpos($requestUri, '/install') === 0;

        if (!$isInstallRoute && file_exists(ROOT_PATH . '/installed.lock')) {
            Session::start();
        }

        // Get request method and URI
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Dispatch the request
        $this->router->dispatch($method, $uri);
    }

    /**
     * Get configuration value
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
     * Check if application is installed
     */
    public static function isInstalled(): bool
    {
        return file_exists(ROOT_PATH . '/installed.lock');
    }

    /**
     * Get the base URL
     */
    public static function baseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
}
