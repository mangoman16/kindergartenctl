<?php
/**
 * =====================================================================================
 * LOGGER - File-Based Logging System
 * =====================================================================================
 *
 * PURPOSE:
 * Centralized logging with severity levels and structured context. Writes to
 * daily-rotated log files in storage/logs/. Used throughout the application
 * for error tracking, security events, and debugging.
 *
 * LOG LEVELS (in order of severity):
 * - ERROR    : Application errors, exceptions, failed operations
 * - WARNING  : Non-critical issues (deprecated features, fallbacks used)
 * - INFO     : Significant events (user created, config saved)
 * - DEBUG    : Detailed diagnostic info (only when APP_DEBUG=true)
 * - SECURITY : Security-relevant events (failed logins, banned IPs, CSRF failures)
 *
 * LOG FORMAT:
 * [2026-01-15 14:30:00] [ERROR] Database connection failed | {"host":"localhost"}
 *
 * FILE NAMING:
 * storage/logs/app-2026-01-15.log (one file per day)
 *
 * USAGE:
 * ```php
 * Logger::error('Database query failed', ['query' => $sql, 'error' => $e->getMessage()]);
 * Logger::security('Failed login attempt', ['ip' => $ip, 'username' => $user]);
 * Logger::info('User created', ['user_id' => $id]);
 * ```
 *
 * AI NOTES:
 * - All methods are static (no instantiation needed)
 * - Context arrays are JSON-encoded and appended to the log line
 * - Security logs go to the same file but with [SECURITY] level for easy grep
 * - Log directory is created automatically if it doesn't exist
 * - Old logs are not auto-rotated/deleted (manual cleanup needed)
 *
 * RELATED FILES:
 * - storage/logs/ - Log file directory
 * - src/config/config.php - APP_DEBUG flag controls DEBUG level output
 * - All core classes use Logger for error reporting
 *
 * @package KindergartenOrganizer\Core
 * @since 1.0.0
 * =====================================================================================
 */

class Logger
{
    // Log levels
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';

    private static ?self $instance = null;
    private string $logDir;
    private string $logFile;
    private bool $enabled = true;

    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
        $this->logDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : __DIR__ . '/../../storage/logs';

        // Create logs directory if it doesn't exist
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }

        // Set default log file name (daily rotation)
        $this->logFile = $this->logDir . '/app-' . date('Y-m-d') . '.log';
    }

    /**
     * Get Logger instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log an error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->log(self::ERROR, $message, $context);
    }

    /**
     * Log a warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->log(self::WARNING, $message, $context);
    }

    /**
     * Log an info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->log(self::INFO, $message, $context);
    }

    /**
     * Log a debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getInstance()->log(self::DEBUG, $message, $context);
    }

    /**
     * Log an exception
     */
    public static function exception(\Throwable $exception, array $context = []): void
    {
        $message = sprintf(
            '%s: %s in %s:%d',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $context['trace'] = $exception->getTraceAsString();

        self::getInstance()->log(self::ERROR, $message, $context);
    }

    /**
     * Log a database query for debugging
     */
    public static function query(string $sql, array $params = []): void
    {
        if (self::getInstance()->shouldLogDebug()) {
            $message = 'SQL Query: ' . $sql;
            $context = !empty($params) ? ['params' => $params] : [];
            self::getInstance()->log(self::DEBUG, $message, $context);
        }
    }

    /**
     * Log authentication events
     */
    public static function auth(string $event, array $context = []): void
    {
        self::getInstance()->log(self::INFO, 'Auth: ' . $event, $context);
    }

    /**
     * Log security events (always logged regardless of debug mode)
     */
    public static function security(string $event, array $context = []): void
    {
        $message = 'SECURITY: ' . $event;
        self::getInstance()->log(self::WARNING, $message, $context, true);
    }

    /**
     * Main logging method
     */
    private function log(string $level, string $message, array $context = [], bool $force = false): void
    {
        if (!$this->enabled && !$force) {
            return;
        }

        // Don't log DEBUG messages unless debug mode is enabled
        if ($level === self::DEBUG && !$this->shouldLogDebug() && !$force) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';

        // Include request information
        $requestInfo = $this->getRequestInfo();

        $logEntry = sprintf(
            "[%s] [%s]%s %s%s\n",
            $timestamp,
            $level,
            $requestInfo,
            $message,
            $contextStr
        );

        // Write to log file
        $this->writeToFile($logEntry);

        // Also write errors to PHP error log for backwards compatibility
        if ($level === self::ERROR) {
            error_log($message);
        }
    }

    /**
     * Write log entry to file
     */
    private function writeToFile(string $entry): void
    {
        try {
            // Ensure log directory exists
            if (!is_dir($this->logDir)) {
                mkdir($this->logDir, 0755, true);
            }

            // Write to file
            file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Fallback to error_log if file writing fails
            error_log('Logger: Failed to write to log file - ' . $e->getMessage());
        }
    }

    /**
     * Get request information for logging
     */
    private function getRequestInfo(): string
    {
        $info = [];

        if (php_sapi_name() !== 'cli') {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
            $uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
            $ip = $this->getClientIp();

            $info[] = "{$method} {$uri}";
            $info[] = "IP: {$ip}";
        } else {
            $info[] = "CLI";
        }

        return !empty($info) ? ' [' . implode(' | ', $info) . ']' : '';
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        // Check for proxy headers (but validate them)
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($forwarded[0]);
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'INVALID_IP';
    }

    /**
     * Check if debug logging should be enabled
     */
    private function shouldLogDebug(): bool
    {
        // Check if App class exists and has debug config
        if (class_exists('App')) {
            return App::config('app.debug', false);
        }

        // Fallback to ini setting
        return (bool)ini_get('display_errors');
    }

    /**
     * Enable logging
     */
    public static function enable(): void
    {
        self::getInstance()->enabled = true;
    }

    /**
     * Disable logging
     */
    public static function disable(): void
    {
        self::getInstance()->enabled = false;
    }

    /**
     * Clean old log files (older than specified days)
     */
    public static function cleanOldLogs(int $days = 30): int
    {
        $logger = self::getInstance();
        $deleted = 0;
        $cutoffTime = time() - ($days * 24 * 60 * 60);

        try {
            $files = glob($logger->logDir . '/app-*.log');

            foreach ($files as $file) {
                if (filemtime($file) < $cutoffTime) {
                    if (unlink($file)) {
                        $deleted++;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('Logger: Failed to clean old logs - ' . $e->getMessage());
        }

        return $deleted;
    }

    /**
     * Get recent log entries
     */
    public static function getRecentLogs(int $lines = 100): array
    {
        $logger = self::getInstance();
        $logs = [];

        try {
            if (file_exists($logger->logFile)) {
                $file = new \SplFileObject($logger->logFile);
                $file->seek(PHP_INT_MAX);
                $totalLines = $file->key();

                $startLine = max(0, $totalLines - $lines);
                $file->seek($startLine);

                while (!$file->eof()) {
                    $line = trim($file->current());
                    if (!empty($line)) {
                        $logs[] = $line;
                    }
                    $file->next();
                }
            }
        } catch (\Throwable $e) {
            error_log('Logger: Failed to read logs - ' . $e->getMessage());
        }

        return $logs;
    }
}
