<?php
/**
 * Database Migration System
 *
 * Simple migration system for tracking and applying database changes.
 */

class Migration
{
    private PDO $db;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->migrationsPath = __DIR__ . '/migrations';
        $this->ensureMigrationsTable();
    }

    /**
     * Create the migrations tracking table if it doesn't exist
     */
    private function ensureMigrationsTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT UNSIGNED NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Get all migration files
     */
    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);
        return $files;
    }

    /**
     * Get list of already executed migrations
     */
    private function getExecutedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable}");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get the next batch number
     */
    private function getNextBatch(): int
    {
        $stmt = $this->db->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM {$this->migrationsTable}");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Run all pending migrations
     */
    public function migrate(): array
    {
        $files = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();
        $batch = $this->getNextBatch();
        $results = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');

            if (in_array($name, $executed)) {
                continue;
            }

            $results[] = $this->runMigration($file, $name, $batch);
        }

        return $results;
    }

    /**
     * Run a single migration file
     */
    private function runMigration(string $file, string $name, int $batch): array
    {
        require_once $file;

        $className = $this->getClassName($name);

        if (!class_exists($className)) {
            return [
                'migration' => $name,
                'status' => 'error',
                'message' => "Class {$className} not found",
            ];
        }

        try {
            $this->db->beginTransaction();

            $migration = new $className($this->db);
            $migration->up();

            // Record the migration
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (:migration, :batch)"
            );
            $stmt->execute(['migration' => $name, 'batch' => $batch]);

            $this->db->commit();

            return [
                'migration' => $name,
                'status' => 'success',
                'message' => 'Migrated successfully',
            ];
        } catch (Exception $e) {
            $this->db->rollBack();

            return [
                'migration' => $name,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Rollback the last batch of migrations
     */
    public function rollback(): array
    {
        $stmt = $this->db->query(
            "SELECT migration FROM {$this->migrationsTable}
             WHERE batch = (SELECT MAX(batch) FROM {$this->migrationsTable})
             ORDER BY id DESC"
        );
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $results = [];

        foreach ($migrations as $name) {
            $results[] = $this->rollbackMigration($name);
        }

        return $results;
    }

    /**
     * Rollback a single migration
     */
    private function rollbackMigration(string $name): array
    {
        $file = $this->migrationsPath . '/' . $name . '.php';

        if (!file_exists($file)) {
            return [
                'migration' => $name,
                'status' => 'error',
                'message' => 'Migration file not found',
            ];
        }

        require_once $file;

        $className = $this->getClassName($name);

        if (!class_exists($className)) {
            return [
                'migration' => $name,
                'status' => 'error',
                'message' => "Class {$className} not found",
            ];
        }

        try {
            $this->db->beginTransaction();

            $migration = new $className($this->db);
            $migration->down();

            // Remove the migration record
            $stmt = $this->db->prepare("DELETE FROM {$this->migrationsTable} WHERE migration = :migration");
            $stmt->execute(['migration' => $name]);

            $this->db->commit();

            return [
                'migration' => $name,
                'status' => 'success',
                'message' => 'Rolled back successfully',
            ];
        } catch (Exception $e) {
            $this->db->rollBack();

            return [
                'migration' => $name,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the migration status
     */
    public function status(): array
    {
        $files = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();
        $status = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');
            $status[] = [
                'migration' => $name,
                'status' => in_array($name, $executed) ? 'executed' : 'pending',
            ];
        }

        return $status;
    }

    /**
     * Convert migration filename to class name
     */
    private function getClassName(string $name): string
    {
        // Remove timestamp prefix (e.g., 2024_01_15_000001_create_users_table)
        $parts = explode('_', $name);

        // Skip the first 4 parts (date/time components)
        if (count($parts) > 4 && is_numeric($parts[0])) {
            array_shift($parts); // year
            array_shift($parts); // month
            array_shift($parts); // day
            array_shift($parts); // sequence
        }

        // Convert to StudlyCase
        return str_replace(' ', '', ucwords(implode(' ', $parts)));
    }

    /**
     * Create a new migration file
     */
    public static function create(string $name): string
    {
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        $content = <<<PHP
<?php
/**
 * Migration: {$name}
 * Created: {$timestamp}
 */

class {$className}
{
    private PDO \$db;

    public function __construct(PDO \$db)
    {
        \$this->db = \$db;
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        // Add your migration SQL here
        // \$this->db->exec("ALTER TABLE ...");
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // Reverse the migration
        // \$this->db->exec("ALTER TABLE ...");
    }
}
PHP;

        $path = __DIR__ . '/migrations/' . $filename;
        file_put_contents($path, $content);

        return $path;
    }
}
