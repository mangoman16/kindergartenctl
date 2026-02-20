<?php
/**
 * =====================================================================================
 * ROUTER - URL Routing and Controller Dispatch
 * =====================================================================================
 *
 * PURPOSE:
 * This class handles URL routing - matching incoming HTTP requests to controller actions.
 * It supports both exact URL matches and pattern matching with named parameters.
 *
 * ROUTE DEFINITION FORMAT:
 * Routes are defined in src/config/routes.php as an associative array:
 * ```php
 * return [
 *     'GET /games' => ['GameController', 'index'],
 *     'GET /games/{id}' => ['GameController', 'show'],
 *     'POST /games/{id}' => ['GameController', 'update'],
 * ];
 * ```
 *
 * URL PATTERN MATCHING:
 * - {id} becomes a named capture group matching any non-slash characters
 * - Parameters are passed to controller methods in order
 * - Example: GET /games/123 → GameController::show('123')
 *
 * REQUEST FLOW:
 * ```
 * Router::dispatch($method, $uri)
 *     ↓
 * match() - Find matching route
 *     ↓
 * Load controller file from src/controllers/
 *     ↓
 * Create controller instance
 *     ↓
 * Call action method with parameters
 * ```
 *
 * SECURITY FEATURES:
 * - redirect() validates URLs to prevent open redirect attacks
 * - back() validates HTTP referer before redirecting
 * - Both methods only allow same-domain redirects
 *
 * KEY METHODS:
 * - dispatch($method, $uri) - Main entry point, routes request to controller
 * - match($method, $uri) - Find matching route and extract parameters
 * - Router::url('/path', ['id' => 123]) - Generate URL with parameters
 * - Router::redirect('/path') - Safe redirect (validates URL)
 * - Router::back() - Redirect to HTTP referer (validated)
 *
 * RELATED FILES:
 * - src/config/routes.php - Route definitions
 * - src/controllers/*.php - Controller classes
 * - src/views/errors/404.php - Not found page
 *
 * AI NOTES:
 * - Route patterns use {param} syntax converted to regex named groups
 * - Controller action parameters are passed as strings (cast to int in controller)
 * - 404 errors are logged via Logger::error()
 * - Query strings are automatically stripped from URIs before matching
 *
 * @package KindergartenOrganizer\Core
 * @since 1.0.0
 * =====================================================================================
 */

class Router
{
    private array $routes = [];
    private array $params = [];

    /**
     * Load routes from configuration file
     */
    public function loadRoutes(): void
    {
        $routesFile = SRC_PATH . '/config/routes.php';
        if (file_exists($routesFile)) {
            $this->routes = require $routesFile;
        }
    }

    /**
     * Add a route
     */
    public function add(string $route, array $handler): void
    {
        $this->routes[$route] = $handler;
    }

    /**
     * Match the current request to a route
     */
    public function match(string $method, string $uri): ?array
    {
        // Remove query string from URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        // Try exact match first
        $routeKey = strtoupper($method) . ' ' . $uri;
        if (isset($this->routes[$routeKey])) {
            return [
                'controller' => $this->routes[$routeKey][0],
                'action' => $this->routes[$routeKey][1],
                'params' => [],
            ];
        }

        // Try pattern matching with parameters
        foreach ($this->routes as $route => $handler) {
            [$routeMethod, $routePattern] = explode(' ', $route, 2);

            if (strtoupper($method) !== strtoupper($routeMethod)) {
                continue;
            }

            // Convert route pattern to regex
            $pattern = $this->convertToRegex($routePattern);

            if (preg_match($pattern, $uri, $matches)) {
                // Extract named parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                return [
                    'controller' => $handler[0],
                    'action' => $handler[1],
                    'params' => $params,
                ];
            }
        }

        return null;
    }

    /**
     * Convert route pattern to regex
     */
    private function convertToRegex(string $pattern): string
    {
        // Escape special characters except {param}
        $pattern = preg_quote($pattern, '#');

        // Convert {param} to named capture groups
        $pattern = preg_replace(
            '/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/',
            '(?P<$1>[^/]+)',
            $pattern
        );

        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch the request to the appropriate controller
     */
    public function dispatch(string $method, string $uri): void
    {
        $match = $this->match($method, $uri);

        if ($match === null) {
            $this->handleNotFound();
            return;
        }

        $controllerName = $match['controller'];
        $actionName = $match['action'];
        $params = $match['params'];

        // Load the controller
        $controllerFile = SRC_PATH . '/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            Logger::error("Controller not found", ['controller' => $controllerName]);
            $this->handleNotFound();
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            Logger::error("Controller class not found", ['controller' => $controllerName]);
            $this->handleNotFound();
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $actionName)) {
            Logger::error("Action not found", [
                'controller' => $controllerName,
                'action' => $actionName
            ]);
            $this->handleNotFound();
            return;
        }

        // Store params for access
        $this->params = $params;

        // Call the action with parameters
        call_user_func_array([$controller, $actionName], $params);
    }

    /**
     * Get current route parameters
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);

        // Check if there's a 404 view
        $notFoundView = SRC_PATH . '/views/errors/404.php';
        if (file_exists($notFoundView)) {
            include $notFoundView;
        } else {
            echo '<h1>' . __('error.404_title') . '</h1>';
            echo '<p>' . __('error.404_text') . '</p>';
            echo '<p><a href="/">' . __('error.404_back') . '</a></p>';
        }
    }

    /**
     * Generate a URL for a given route
     */
    public static function url(string $path, array $params = []): string
    {
        $url = '/' . ltrim($path, '/');

        // Replace route parameters
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', (string)$value, $url);
        }

        return $url;
    }

    /**
     * Redirect to a URL
     * Security: Validates URL to prevent open redirect attacks
     */
    public static function redirect(string $url): void
    {
        // If URL is relative (starts with /), allow it
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            header('Location: ' . $url);
            exit;
        }

        // For absolute URLs, validate the host matches our domain
        $urlHost = parse_url($url, PHP_URL_HOST);
        $serverHost = $_SERVER['HTTP_HOST'] ?? '';

        // Strip port from server host for comparison
        $serverHost = preg_replace('/:\d+$/', '', $serverHost);

        // If no host in URL or host matches server, allow redirect
        if ($urlHost === null || $urlHost === $serverHost) {
            header('Location: ' . $url);
            exit;
        }

        // For external URLs, redirect to home page instead (prevent open redirect)
        header('Location: /');
        exit;
    }

    /**
     * Redirect back to the previous page
     * Security: Validates referer to prevent open redirect attacks
     */
    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';

        // Validate referer is from the same domain to prevent open redirect
        $refererHost = parse_url($referer, PHP_URL_HOST);
        $serverHost = $_SERVER['HTTP_HOST'] ?? '';

        // Strip port from server host for comparison
        $serverHost = preg_replace('/:\d+$/', '', $serverHost);

        if ($refererHost !== null && $refererHost !== $serverHost) {
            $referer = '/';
        }

        self::redirect($referer);
    }
}
