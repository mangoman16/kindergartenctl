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
     * Allowed charsets for database creation
     */
    private static array $allowedCharsets = ['utf8', 'utf8mb4', 'latin1', 'ascii'];

    /**
     * Allowed collations for database creation
     */
    private static array $allowedCollations = [
        'utf8_general_ci', 'utf8_unicode_ci',
        'utf8mb4_general_ci', 'utf8mb4_unicode_ci', 'utf8mb4_0900_ai_ci',
        'latin1_swedish_ci', 'latin1_general_ci',
        'ascii_general_ci'
    ];

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
    }

    /**
     * Validate database name to prevent SQL injection
     * Only allows alphanumeric characters and underscores
     */
    private static function isValidDatabaseName(string $name): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) === 1 && strlen($name) <= 64;
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
            Logger::error('Database connection failed', [
                'error' => $e->getMessage(),
                'host' => $config['host'] ?? 'localhost',
                'database' => $config['database'] ?? 'UNKNOWN'
            ]);
            throw new Exception('Datenbankverbindung fehlgeschlagen');
        }
    }

    /**
     * Test database connection with given credentials
     */
    public static function testConnection(array $config): bool
    {
        $result = self::testConnectionWithDetails($config);
        return $result['success'];
    }

    /**
     * Test database connection with given credentials and return detailed result
     */
    public static function testConnectionWithDetails(array $config): array
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
                // Security: Validate database name to prevent SQL injection
                if (!self::isValidDatabaseName($config['database'])) {
                    Logger::security('Invalid database name format attempted in testConnection', [
                        'database' => $config['database']
                    ]);
                    return [
                        'success' => false,
                        'message' => 'Ungültiger Datenbankname. Nur Buchstaben, Zahlen und Unterstriche erlaubt.'
                    ];
                }

                // Check if database exists, if not we'll create it later
                try {
                    $pdo->exec("USE `{$config['database']}`");
                } catch (PDOException $e) {
                    // Database doesn't exist yet, but connection works
                    return [
                        'success' => true,
                        'message' => 'Verbindung erfolgreich. Datenbank wird bei der Installation erstellt.'
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'Datenbankverbindung erfolgreich!'
            ];
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            // Provide user-friendly error messages
            if (str_contains($errorMessage, 'Access denied')) {
                return [
                    'success' => false,
                    'message' => 'Zugriff verweigert. Benutzername oder Passwort falsch.'
                ];
            } elseif (str_contains($errorMessage, 'Unknown MySQL server host') || str_contains($errorMessage, 'Connection refused')) {
                return [
                    'success' => false,
                    'message' => 'Server nicht erreichbar. Bitte Host und Port prüfen.'
                ];
            } elseif (str_contains($errorMessage, 'Unknown database')) {
                return [
                    'success' => true,
                    'message' => 'Verbindung erfolgreich. Datenbank wird bei der Installation erstellt.'
                ];
            }

            return [
                'success' => false,
                'message' => 'Verbindung fehlgeschlagen: ' . $errorMessage
            ];
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

            // Security: Validate database name to prevent SQL injection
            if (!self::isValidDatabaseName($dbName)) {
                Logger::security('Invalid database name format attempted', ['database' => $dbName]);
                return false;
            }

            // Security: Validate charset against whitelist
            if (!in_array($charset, self::$allowedCharsets, true)) {
                Logger::warning('Invalid charset specified, using default', [
                    'requested' => $charset,
                    'default' => 'utf8mb4'
                ]);
                $charset = 'utf8mb4';
            }

            // Security: Validate collation against whitelist
            if (!in_array($collation, self::$allowedCollations, true)) {
                Logger::warning('Invalid collation specified, using default', [
                    'requested' => $collation,
                    'default' => 'utf8mb4_unicode_ci'
                ]);
                $collation = 'utf8mb4_unicode_ci';
            }

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}");

            return true;
        } catch (PDOException $e) {
            Logger::error('Failed to create database', [
                'error' => $e->getMessage(),
                'database' => $dbName
            ]);
            return false;
        }
    }

    /**
     * Run schema creation SQL with proper error handling
     */
    public static function runSchema(): bool
    {
        $pdo = self::getInstance();
        if (!$pdo) {
            Logger::error('Schema creation failed: No database connection');
            return false;
        }

        $statements = self::getSchemaStatements();

        try {
            // Execute each statement individually for better error reporting
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }

            // Verify all tables were created
            if (!self::verifySchema($pdo)) {
                Logger::error('Schema verification failed: Not all tables were created');
                return false;
            }

            return true;
        } catch (PDOException $e) {
            Logger::error('Schema creation failed', [
                'error' => $e->getMessage(),
                'statement' => substr($statement ?? '', 0, 100)
            ]);
            return false;
        }
    }

    /**
     * Verify that all required tables exist
     */
    private static function verifySchema(PDO $pdo): bool
    {
        $requiredTables = [
            'users', 'categories', 'boxes', 'tags', 'materials', 'games',
            'game_materials', 'game_categories', 'game_tags', 'groups',
            'group_games', 'group_materials', 'calendar_events', 'changelog',
            'ip_bans', 'password_resets', 'settings', 'transactions'
        ];

        $stmt = $pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($requiredTables as $table) {
            if (!in_array($table, $existingTables)) {
                Logger::error("Missing table: {$table}");
                return false;
            }
        }

        return true;
    }

    /**
     * Get schema as array of individual statements
     */
    private static function getSchemaStatements(): array
    {
        $schema = self::getSchema();
        // Split by semicolon but not within strings
        $statements = preg_split('/;(?=([^\']*\'[^\']*\')*[^\']*$)/', $schema);
        return array_filter(array_map('trim', $statements));
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
    quantity INT UNSIGNED DEFAULT 0,
    is_consumable BOOLEAN DEFAULT FALSE,
    status ENUM('complete', 'incomplete', 'damaged', 'missing') DEFAULT 'complete',
    image_path VARCHAR(255) NULL,
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_material_name (name),
    FOREIGN KEY (box_id) REFERENCES boxes(id) ON DELETE SET NULL,
    FULLTEXT INDEX ft_materials (name, description, notes),
    INDEX idx_consumable (is_consumable)
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
    group_id INT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    all_day BOOLEAN DEFAULT TRUE,
    color VARCHAR(7) NULL,
    event_type ENUM('played', 'planned') DEFAULT 'planned',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE SET NULL,
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE SET NULL,
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date),
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

-- Transactions table for data integrity verification (like online shop transaction log)
CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(64) NOT NULL UNIQUE,
    user_id INT UNSIGNED NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED NULL,
    operation ENUM('create', 'update', 'delete', 'batch') NOT NULL,
    data_before JSON NULL,
    data_after JSON NULL,
    checksum VARCHAR(64) NOT NULL,
    status ENUM('pending', 'committed', 'rolled_back', 'verified', 'failed') DEFAULT 'pending',
    verified_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
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
     *
     * @param array $config Database configuration
     * @return array Result with 'success' boolean and 'message' string
     */
    public static function saveConfig(array $config): array
    {
        $configFile = SRC_PATH . '/config/database.php';
        $configDir = dirname($configFile);

        // Check if config directory exists
        if (!is_dir($configDir)) {
            if (!@mkdir($configDir, 0755, true)) {
                Logger::error('Failed to create config directory', ['path' => $configDir]);
                return [
                    'success' => false,
                    'message' => 'Konfigurationsverzeichnis konnte nicht erstellt werden: ' . $configDir
                ];
            }
        }

        // Check if config directory is writable
        if (!is_writable($configDir)) {
            Logger::error('Config directory not writable', ['path' => $configDir]);
            return [
                'success' => false,
                'message' => 'Konfigurationsverzeichnis ist nicht beschreibbar: ' . $configDir
            ];
        }

        // Check if config file exists and is writable
        if (file_exists($configFile) && !is_writable($configFile)) {
            Logger::error('Config file not writable', ['path' => $configFile]);
            return [
                'success' => false,
                'message' => 'Konfigurationsdatei ist nicht beschreibbar: ' . $configFile
            ];
        }

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

        // Attempt to write with error suppression, check result
        $bytesWritten = @file_put_contents($configFile, $configContent, LOCK_EX);

        if ($bytesWritten === false) {
            $error = error_get_last();
            $errorMsg = $error['message'] ?? 'Unbekannter Fehler';
            Logger::error('Failed to write config file', [
                'path' => $configFile,
                'error' => $errorMsg
            ]);
            return [
                'success' => false,
                'message' => 'Konfigurationsdatei konnte nicht geschrieben werden: ' . $errorMsg
            ];
        }

        // Verify the file was written correctly
        if (!file_exists($configFile)) {
            Logger::error('Config file does not exist after write', ['path' => $configFile]);
            return [
                'success' => false,
                'message' => 'Konfigurationsdatei existiert nicht nach dem Schreiben'
            ];
        }

        Logger::info('Database configuration saved successfully', ['path' => $configFile]);
        return [
            'success' => true,
            'message' => 'Konfiguration erfolgreich gespeichert'
        ];
    }

    /**
     * Save database configuration to file (legacy boolean return)
     * @deprecated Use saveConfig() which returns detailed result array
     */
    public static function saveConfigLegacy(array $config): bool
    {
        $result = self::saveConfig($config);
        return $result['success'];
    }

    /**
     * Close the database connection
     */
    public static function close(): void
    {
        self::$instance = null;
    }
}
