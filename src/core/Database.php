<?php
/**
 * Database Connection Class
 * Singleton pattern for PDO connection management
 */

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
    }

    /**
     * Get the database connection instance
     */
    public static function getInstance(): ?PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Load configuration from file
     */
    public static function loadConfig(): array
    {
        if (empty(self::$config)) {
            $configFile = SRC_PATH . '/config/database.php';
            if (file_exists($configFile)) {
                self::$config = require $configFile;
            }
        }
        return self::$config;
    }

    /**
     * Set configuration programmatically (for installation)
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
        self::$instance = null; // Reset connection
    }

    /**
     * Connect to the database
     */
    private static function connect(): void
    {
        $config = self::loadConfig();

        if (empty($config['database'])) {
            return; // No database configured yet
        }

        try {
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $config['driver'] ?? 'mysql',
                $config['host'] ?? 'localhost',
                $config['port'] ?? 3306,
                $config['database'],
                $config['charset'] ?? 'utf8mb4'
            );

            self::$instance = new PDO(
                $dsn,
                $config['username'] ?? '',
                $config['password'] ?? '',
                $config['options'] ?? [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Datenbankverbindung fehlgeschlagen');
        }
    }

    /**
     * Test database connection with given credentials
     */
    public static function testConnection(array $config): bool
    {
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%d',
                $config['driver'] ?? 'mysql',
                $config['host'] ?? 'localhost',
                $config['port'] ?? 3306
            );

            $pdo = new PDO(
                $dsn,
                $config['username'] ?? '',
                $config['password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Try to select the database if specified
            if (!empty($config['database'])) {
                $pdo->exec("USE `{$config['database']}`");
            }

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Create a database if it doesn't exist
     */
    public static function createDatabase(array $config): bool
    {
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%d',
                $config['driver'] ?? 'mysql',
                $config['host'] ?? 'localhost',
                $config['port'] ?? 3306
            );

            $pdo = new PDO(
                $dsn,
                $config['username'] ?? '',
                $config['password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $dbName = $config['database'];
            $charset = $config['charset'] ?? 'utf8mb4';
            $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}");

            return true;
        } catch (PDOException $e) {
            error_log('Failed to create database: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Run schema creation SQL
     */
    public static function runSchema(): bool
    {
        $pdo = self::getInstance();
        if (!$pdo) {
            return false;
        }

        $schema = self::getSchema();

        try {
            $pdo->exec($schema);
            return true;
        } catch (PDOException $e) {
            error_log('Schema creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the database schema SQL
     */
    public static function getSchema(): string
    {
        return "
-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    remember_token VARCHAR(255) NULL,
    remember_token_expires_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories (Age Groups)
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT INDEX ft_categories (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Boxes
CREATE TABLE IF NOT EXISTS boxes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    number VARCHAR(20) NULL,
    label VARCHAR(50) NULL,
    location VARCHAR(255) NULL,
    description TEXT NULL,
    notes TEXT NULL,
    image_path VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_box_name (name),
    FULLTEXT INDEX ft_boxes (name, location, description, notes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags (Themes)
CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    color VARCHAR(7) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT INDEX ft_tags (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Materials
CREATE TABLE IF NOT EXISTS materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    notes TEXT NULL,
    box_id INT UNSIGNED NULL,
    status ENUM('complete', 'incomplete', 'damaged', 'missing') DEFAULT 'complete',
    image_path VARCHAR(255) NULL,
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_material_name (name),
    FOREIGN KEY (box_id) REFERENCES boxes(id) ON DELETE SET NULL,
    FULLTEXT INDEX ft_materials (name, description, notes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Games
CREATE TABLE IF NOT EXISTS games (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    instructions TEXT NULL,
    min_players INT UNSIGNED NULL,
    max_players INT UNSIGNED NULL,
    duration_minutes INT UNSIGNED NULL,
    difficulty TINYINT UNSIGNED DEFAULT 1,
    image_path VARCHAR(255) NULL,
    is_favorite BOOLEAN DEFAULT FALSE,
    is_outdoor BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    box_id INT UNSIGNED NULL,
    category_id INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_game_name (name),
    FULLTEXT INDEX ft_games (name, description, instructions),
    INDEX idx_difficulty (difficulty),
    INDEX idx_favorite (is_favorite),
    INDEX idx_active (is_active),
    INDEX idx_outdoor (is_outdoor),
    FOREIGN KEY (box_id) REFERENCES boxes(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Game Materials junction table
CREATE TABLE IF NOT EXISTS game_materials (
    game_id INT UNSIGNED NOT NULL,
    material_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED DEFAULT 1,
    PRIMARY KEY (game_id, material_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Game Categories junction table
CREATE TABLE IF NOT EXISTS game_categories (
    game_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (game_id, category_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Game Tags junction table
CREATE TABLE IF NOT EXISTS game_tags (
    game_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (game_id, tag_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Groups (Virtual Collections)
CREATE TABLE IF NOT EXISTS `groups` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT INDEX ft_groups (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Group Games junction table
CREATE TABLE IF NOT EXISTS group_games (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT UNSIGNED NOT NULL,
    game_id INT UNSIGNED NOT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_game (group_id, game_id),
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    INDEX idx_sort (group_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Group Materials junction table
CREATE TABLE IF NOT EXISTS group_materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT UNSIGNED NOT NULL,
    material_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED DEFAULT 1,
    sort_order INT UNSIGNED DEFAULT 0,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_material (group_id, material_id),
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    INDEX idx_sort (group_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calendar Events
CREATE TABLE IF NOT EXISTS calendar_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id INT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    event_date DATE NOT NULL,
    event_type ENUM('played', 'planned') NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE SET NULL,
    INDEX idx_date (event_date),
    INDEX idx_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Changelog
CREATE TABLE IF NOT EXISTS changelog (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    entity_name VARCHAR(200) NULL,
    action ENUM('create', 'update', 'delete', 'move') NOT NULL,
    changes JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IP Bans
CREATE TABLE IF NOT EXISTS ip_bans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    failed_attempts INT UNSIGNED DEFAULT 0,
    last_attempt_at DATETIME NULL,
    banned_until DATETIME NULL,
    is_permanent BOOLEAN DEFAULT FALSE,
    reason VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ip (ip_address),
    INDEX idx_banned (banned_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Resets
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token_hash),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories (age groups)
INSERT IGNORE INTO categories (name, description, sort_order) VALUES
    ('2-3 Jahre', 'Spiele für Kinder von 2-3 Jahren', 1),
    ('3-4 Jahre', 'Spiele für Kinder von 3-4 Jahren', 2),
    ('4-5 Jahre', 'Spiele für Kinder von 4-5 Jahren', 3),
    ('5-6 Jahre', 'Spiele für Kinder von 5-6 Jahren', 4);

-- Insert default settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('items_per_page', '24'),
    ('default_view', 'grid');
";
    }

    /**
     * Save database configuration to file
     */
    public static function saveConfig(array $config): bool
    {
        $configContent = "<?php
/**
 * Database Configuration
 * Generated during installation
 */

return [
    'driver' => 'mysql',
    'host' => " . var_export($config['host'] ?? 'localhost', true) . ",
    'port' => " . var_export((int)($config['port'] ?? 3306), true) . ",
    'database' => " . var_export($config['database'] ?? '', true) . ",
    'username' => " . var_export($config['username'] ?? '', true) . ",
    'password' => " . var_export($config['password'] ?? '', true) . ",
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci\",
    ],
];
";

        $configFile = SRC_PATH . '/config/database.php';
        return file_put_contents($configFile, $configContent) !== false;
    }

    /**
     * Close the database connection
     */
    public static function close(): void
    {
        self::$instance = null;
    }
}
