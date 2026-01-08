<?php
/**
 * Global Helper Functions
 */

/**
 * Escape output for HTML
 */
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get translation string
 */
function __(?string $key, array $replace = []): string
{
    static $lang = null;

    if ($lang === null) {
        $langFile = SRC_PATH . '/lang/de.php';
        if (file_exists($langFile)) {
            $lang = require $langFile;
        } else {
            $lang = [];
        }
    }

    $text = $lang[$key] ?? $key;

    foreach ($replace as $search => $value) {
        $text = str_replace(':' . $search, (string)$value, $text);
    }

    return $text;
}

/**
 * Generate a URL
 */
function url(string $path = '', array $params = []): string
{
    $url = '/' . ltrim($path, '/');

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}

/**
 * Generate an asset URL
 */
function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

/**
 * Generate an upload URL
 */
function upload(string $path): string
{
    return '/uploads/' . ltrim($path, '/');
}

/**
 * Check if the current URL matches
 */
function isUrl(string $path): bool
{
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return $currentPath === '/' . ltrim($path, '/');
}

/**
 * Check if current URL starts with path
 */
function isActiveNav(string $path): bool
{
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = '/' . ltrim($path, '/');

    if ($path === '/') {
        return $currentPath === '/';
    }

    return strpos($currentPath, $path) === 0;
}

/**
 * Generate CSRF input field
 */
function csrfField(): string
{
    $token = Session::csrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Get old input value
 */
function old(string $key, $default = ''): string
{
    return e((string)(Session::oldInput($key) ?? $default));
}

/**
 * Check if there's an error for a field
 */
function hasError(string $field, array $errors): bool
{
    return isset($errors[$field]);
}

/**
 * Get error message for a field
 */
function getError(string $field, array $errors): string
{
    return e($errors[$field][0] ?? '');
}

/**
 * Dump and die (for debugging)
 */
function dd(...$vars): void
{
    echo '<pre>';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}

/**
 * Get configuration value
 */
function config(string $key, $default = null)
{
    return App::config($key, $default);
}

/**
 * Get the current user
 */
function currentUser(): ?array
{
    return Auth::user();
}

/**
 * Check if user is authenticated
 */
function isAuthenticated(): bool
{
    return Auth::check();
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Format file size
 */
function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

    return number_format($bytes / pow(1024, $power), 2, ',', '.') . ' ' . $units[$power];
}

/**
 * Generate a random string
 */
function randomString(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Pluralize a word (simple German support)
 */
function pluralize(int $count, string $singular, string $plural): string
{
    return $count === 1 ? $singular : $plural;
}

/**
 * Get the image path or a placeholder
 */
function imagePath(?string $path, string $type = 'games', string $size = 'thumb'): string
{
    if ($path && file_exists(UPLOADS_PATH . '/' . $path)) {
        return upload($path);
    }

    // Return placeholder
    return asset("images/ui/placeholder-{$type}.png");
}

/**
 * Format difficulty as stars or text
 */
function formatDifficulty(int $level, string $format = 'stars'): string
{
    if ($format === 'stars') {
        return str_repeat('★', $level) . str_repeat('☆', 3 - $level);
    }

    $labels = [
        1 => 'Leicht',
        2 => 'Mittel',
        3 => 'Schwer',
    ];

    return $labels[$level] ?? '';
}

/**
 * Format status with color class
 */
function statusClass(string $status): string
{
    $classes = [
        'complete' => 'status-success',
        'incomplete' => 'status-warning',
        'damaged' => 'status-danger',
        'missing' => 'status-danger',
        'active' => 'status-success',
        'archived' => 'status-muted',
        'needs_materials' => 'status-warning',
        'played' => 'status-success',
        'planned' => 'status-info',
    ];

    return $classes[$status] ?? '';
}

/**
 * Convert array to HTML attributes
 */
function htmlAttributes(array $attributes): string
{
    $html = [];

    foreach ($attributes as $key => $value) {
        if ($value === true) {
            $html[] = e($key);
        } elseif ($value !== false && $value !== null) {
            $html[] = e($key) . '="' . e((string)$value) . '"';
        }
    }

    return implode(' ', $html);
}

/**
 * Create pagination HTML
 */
function pagination(int $currentPage, int $totalPages, string $baseUrl): string
{
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<nav class="pagination">';
    $html .= '<ul class="pagination-list">';

    // Previous button
    if ($currentPage > 1) {
        $html .= '<li><a href="' . e($baseUrl . '?page=' . ($currentPage - 1)) . '" class="pagination-link">&laquo; Zurück</a></li>';
    }

    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $html .= '<li><a href="' . e($baseUrl . '?page=1') . '" class="pagination-link">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="pagination-ellipsis">...</li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $activeClass = $i === $currentPage ? ' active' : '';
        $html .= '<li><a href="' . e($baseUrl . '?page=' . $i) . '" class="pagination-link' . $activeClass . '">' . $i . '</a></li>';
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="pagination-ellipsis">...</li>';
        }
        $html .= '<li><a href="' . e($baseUrl . '?page=' . $totalPages) . '" class="pagination-link">' . $totalPages . '</a></li>';
    }

    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . e($baseUrl . '?page=' . ($currentPage + 1)) . '" class="pagination-link">Weiter &raquo;</a></li>';
    }

    $html .= '</ul></nav>';

    return $html;
}

/**
 * Log a message to file
 */
function logMessage(string $message, string $level = 'info'): void
{
    $logFile = STORAGE_PATH . '/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

    file_put_contents($logFile, $formattedMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Get user preference value
 */
function userPreference(string $key, $default = null)
{
    static $preferences = null;

    if ($preferences === null) {
        $configPath = STORAGE_PATH . '/preferences.php';
        if (file_exists($configPath)) {
            $preferences = include $configPath;
        } else {
            $preferences = [
                'items_per_page' => 24,
                'default_view' => 'grid',
            ];
        }
    }

    return $preferences[$key] ?? $default;
}

/**
 * Get items per page preference
 */
function getItemsPerPage(): int
{
    return (int)userPreference('items_per_page', 24);
}
