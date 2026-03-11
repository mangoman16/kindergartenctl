<?php
/**
 * Application Bootstrap - Web Entry Point
 *
 * Delegates shared setup (config, environment, models, services) to AppBoot,
 * then loads web-only classes (Router, Session, Controller, Auth) and dispatches
 * the HTTP request.
 *
 * @package KindergartenOrganizer\Core
 * @since 1.0.0
 */

class App
{
    private Router $router;

    /**
     * Initialize the application.
     */
    public function __construct()
    {
        // Shared bootstrap (config, environment, DB, models, services)
        require_once SRC_PATH . '/core/AppBoot.php';
        AppBoot::boot();

        // Web-only core classes
        require_once SRC_PATH . '/core/Router.php';
        require_once SRC_PATH . '/core/Session.php';
        require_once SRC_PATH . '/core/Controller.php';
        require_once SRC_PATH . '/core/Auth.php';

        $this->router = new Router();
        $this->router->loadRoutes();
    }

    /**
     * Run the web application.
     */
    public function run(): void
    {
        Session::start();

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        $this->router->dispatch($method, $uri);
    }

    /**
     * Get configuration value using dot notation.
     * Delegates to AppBoot which holds the config.
     */
    public static function config(string $key, $default = null)
    {
        return AppBoot::config($key, $default);
    }

    /**
     * Check if application is installed.
     */
    public static function isInstalled(): bool
    {
        return AppBoot::isInstalled();
    }

    /**
     * Get the base URL.
     */
    public static function baseUrl(): string
    {
        return AppBoot::baseUrl();
    }
}
