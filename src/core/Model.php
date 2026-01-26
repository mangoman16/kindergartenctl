<?php
/**
 * Base Model Class
 */

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected PDO $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get PDO instance
     */
    protected static function getDb(): PDO
    {
        return Database::getInstance();
    }

    /**
     * Validate a column name to prevent SQL injection
     * Column must be alphanumeric with underscores only
     */
    protected static function validateColumn(string $column): bool
    {
        // Column must match pattern: letters, numbers, underscores
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            return false;
        }

        // If fillable is defined, column must be in fillable or be the primary key
        if (!empty(static::$fillable)) {
            return in_array($column, static::$fillable, true) || $column === static::$primaryKey;
        }

        return true;
    }

    /**
     * Validate column name and throw exception if invalid
     */
    protected static function assertValidColumn(string $column): void
    {
        if (!self::validateColumn($column)) {
            throw new InvalidArgumentException("Invalid column name: {$column}");
        }
    }

    /**
     * Find a record by ID
     */
    public static function find(int $id): ?array
    {
        $db = self::getDb();
        $table = static::$table;
        $pk = static::$primaryKey;

        $stmt = $db->prepare("SELECT * FROM `{$table}` WHERE `{$pk}` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find a record by a specific column
     */
    public static function findBy(string $column, $value): ?array
    {
        self::assertValidColumn($column);

        $db = self::getDb();
        $table = static::$table;

        $stmt = $db->prepare("SELECT * FROM `{$table}` WHERE `{$column}` = :value LIMIT 1");
        $stmt->execute(['value' => $value]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all records
     */
    public static function all(string $orderBy = null, string $direction = 'ASC'): array
    {
        $db = self::getDb();
        $table = static::$table;

        $sql = "SELECT * FROM `{$table}`";
        if ($orderBy) {
            self::assertValidColumn($orderBy);
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY `{$orderBy}` {$direction}";
        }

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get records with pagination
     */
    public static function paginate(int $page = 1, int $perPage = 24, string $orderBy = null, string $direction = 'ASC'): array
    {
        $db = self::getDb();
        $table = static::$table;

        // Get total count
        $countStmt = $db->query("SELECT COUNT(*) FROM `{$table}`");
        $total = (int)$countStmt->fetchColumn();

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Build query
        $sql = "SELECT * FROM `{$table}`";
        if ($orderBy) {
            self::assertValidColumn($orderBy);
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY `{$orderBy}` {$direction}";
        }
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Create a new record
     * Returns the new ID on success, null on failure (including duplicate key violations)
     */
    public static function create(array $data): ?int
    {
        $db = self::getDb();
        $table = static::$table;

        // Filter to only fillable fields
        if (!empty(static::$fillable)) {
            $data = array_intersect_key($data, array_flip(static::$fillable));
        }

        if (empty($data)) {
            return null;
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
            return (int)$db->lastInsertId();
        } catch (PDOException $e) {
            // Handle duplicate key violation (MySQL error 1062)
            if ($e->errorInfo[1] === 1062) {
                Logger::warning('Duplicate key violation in create', [
                    'table' => $table,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
            // Re-throw other exceptions
            throw $e;
        }
    }

    /**
     * Update a record
     * Returns true on success, false on failure (including duplicate key violations)
     */
    public static function update(int $id, array $data): bool
    {
        $db = self::getDb();
        $table = static::$table;
        $pk = static::$primaryKey;

        // Filter to only fillable fields
        if (!empty(static::$fillable)) {
            $data = array_intersect_key($data, array_flip(static::$fillable));
        }

        if (empty($data)) {
            return false;
        }

        $sets = array_map(fn($col) => "`{$col}` = :{$col}", array_keys($data));
        $data[$pk] = $id;

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = :%s",
            $table,
            implode(', ', $sets),
            $pk,
            $pk
        );

        try {
            $stmt = $db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            // Handle duplicate key violation (MySQL error 1062)
            if ($e->errorInfo[1] === 1062) {
                Logger::warning('Duplicate key violation in update', [
                    'table' => $table,
                    'id' => $id,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
            // Re-throw other exceptions
            throw $e;
        }
    }

    /**
     * Delete a record
     */
    public static function delete(int $id): bool
    {
        $db = self::getDb();
        $table = static::$table;
        $pk = static::$primaryKey;

        $stmt = $db->prepare("DELETE FROM `{$table}` WHERE `{$pk}` = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Check if a record exists
     */
    public static function exists(int $id): bool
    {
        $db = self::getDb();
        $table = static::$table;
        $pk = static::$primaryKey;

        $stmt = $db->prepare("SELECT 1 FROM `{$table}` WHERE `{$pk}` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Count all records
     */
    public static function count(): int
    {
        $db = self::getDb();
        $table = static::$table;

        $stmt = $db->query("SELECT COUNT(*) FROM `{$table}`");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Count records with a condition
     */
    public static function countWhere(string $column, $value): int
    {
        self::assertValidColumn($column);

        $db = self::getDb();
        $table = static::$table;

        $stmt = $db->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :value");
        $stmt->execute(['value' => $value]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get records where column equals value
     */
    public static function where(string $column, $value, string $orderBy = null, string $direction = 'ASC'): array
    {
        self::assertValidColumn($column);

        $db = self::getDb();
        $table = static::$table;

        $sql = "SELECT * FROM `{$table}` WHERE `{$column}` = :value";
        if ($orderBy) {
            self::assertValidColumn($orderBy);
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY `{$orderBy}` {$direction}";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute(['value' => $value]);

        return $stmt->fetchAll();
    }

    /**
     * Fulltext search
     */
    public static function search(string $query, array $columns, int $limit = 50): array
    {
        // Validate all column names
        foreach ($columns as $column) {
            self::assertValidColumn($column);
        }

        $db = self::getDb();
        $table = static::$table;

        $columnList = implode(', ', array_map(fn($c) => "`{$c}`", $columns));

        $sql = "SELECT *, MATCH({$columnList}) AGAINST(:query IN BOOLEAN MODE) AS relevance
                FROM `{$table}`
                WHERE MATCH({$columnList}) AGAINST(:query IN BOOLEAN MODE)
                ORDER BY relevance DESC
                LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue('query', $query . '*', PDO::PARAM_STR);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Execute a raw query
     */
    public static function query(string $sql, array $params = []): array
    {
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a raw statement
     */
    public static function execute(string $sql, array $params = []): bool
    {
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Check if a value exists in a column (for duplicate detection)
     */
    public static function valueExists(string $column, $value, ?int $excludeId = null): bool
    {
        self::assertValidColumn($column);

        $db = self::getDb();
        $table = static::$table;
        $pk = static::$primaryKey;

        $sql = "SELECT 1 FROM `{$table}` WHERE `{$column}` = :value";
        $params = ['value' => $value];

        if ($excludeId !== null) {
            $sql .= " AND `{$pk}` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Begin a transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getDb()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public static function commit(): bool
    {
        return self::getDb()->commit();
    }

    /**
     * Rollback a transaction
     */
    public static function rollback(): bool
    {
        return self::getDb()->rollBack();
    }
}
