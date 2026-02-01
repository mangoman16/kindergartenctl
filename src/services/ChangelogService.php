<?php
/**
 * =====================================================================================
 * CHANGELOG SERVICE - Audit Trail System
 * =====================================================================================
 *
 * PURPOSE:
 * This service provides a centralized audit logging system for all entity changes
 * in the application. It tracks create, update, and delete operations across all
 * major entities (games, materials, boxes, categories, tags, groups, events).
 *
 * ARCHITECTURE:
 * - Singleton pattern ensures one instance manages all changelog operations
 * - Integrates with Auth system to capture user context
 * - Stores changes as JSON for flexible field tracking
 *
 * DATABASE TABLE: changelog
 * - user_id: Who made the change (NULL if during installation)
 * - entity_type: 'game', 'material', 'box', 'category', 'tag', 'group', 'event'
 * - entity_id: The primary key of the changed entity
 * - entity_name: Human-readable name (preserved for display after deletion)
 * - action: 'create', 'update', 'delete'
 * - changes: JSON object with old/new values for each changed field
 * - created_at: Timestamp of the change
 *
 * USAGE PATTERN:
 * ```php
 * $changelog = ChangelogService::getInstance();
 *
 * // On create
 * $changelog->logCreate('game', $gameId, $game['name'], ['difficulty' => 1]);
 *
 * // On update (with change tracking)
 * $changes = $changelog->getChanges($oldData, $newData, ['name', 'description']);
 * $changelog->logUpdate('game', $gameId, $game['name'], $changes);
 *
 * // On delete
 * $changelog->logDelete('game', $gameId, $game['name']);
 * ```
 *
 * AI NOTES:
 * - All SQL queries use parameterized statements (PDO prepared statements)
 * - The 'username' column in users table maps to 'user_name' alias in queries
 * - JSON changes use JSON_UNESCAPED_UNICODE for German character support
 * - Cleanup method respects data retention policies (default 365 days)
 *
 * RELATED FILES:
 * - src/controllers/ChangelogController.php - View/manage changelog entries
 * - src/views/changelog/index.php - Changelog list view
 * - Database table definition in src/core/Database.php getSchema()
 *
 * @package KindergartenOrganizer\Services
 * @since 1.0.0
 * =====================================================================================
 */

class ChangelogService
{
    /**
     * Singleton instance holder
     *
     * AI NOTE: This ensures all changelog operations go through one instance,
     * maintaining consistency and reducing database connection overhead.
     *
     * @var ChangelogService|null
     */
    private static ?ChangelogService $instance = null;

    /**
     * Database connection (PDO instance)
     *
     * AI NOTE: Obtained from Database::getInstance() which manages
     * the connection pool and configuration.
     *
     * @var PDO
     */
    private PDO $db;

    /**
     * Private constructor for singleton pattern
     *
     * AI NOTE: Cannot be called directly - use getInstance() instead.
     * Initializes database connection on first instantiation.
     */
    private function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get the singleton instance
     *
     * AI NOTE: Thread-safe singleton pattern. Creates instance on first call,
     * returns existing instance on subsequent calls.
     *
     * USAGE:
     * ```php
     * $changelog = ChangelogService::getInstance();
     * ```
     *
     * @return ChangelogService The singleton instance
     */
    public static function getInstance(): ChangelogService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Core logging method - records a change to the changelog table
     *
     * AI NOTE: This is the base method called by all other log methods.
     * It captures the current authenticated user automatically via Auth::id().
     *
     * PARAMETERS EXPLAINED:
     * - $entityType: String identifier for the entity ('game', 'material', etc.)
     * - $entityId: The primary key (id column) of the affected entity
     * - $entityName: Human-readable name for display purposes
     * - $action: One of 'create', 'update', 'delete'
     * - $data: Associative array of changes (serialized to JSON)
     * - $userId: Override user (null = use current authenticated user)
     *
     * ERROR HANDLING:
     * - Catches all exceptions to prevent audit logging from breaking operations
     * - Logs errors to application log via Logger::error()
     *
     * @param string $entityType Entity type identifier
     * @param int $entityId Primary key of entity
     * @param string $entityName Display name of entity
     * @param string $action Action performed
     * @param array $data Change data (old/new values)
     * @param int|null $userId Override user ID (optional)
     * @return bool Success status
     */
    public function log(
        string $entityType,
        int $entityId,
        string $entityName,
        string $action,
        array $data = [],
        ?int $userId = null
    ): bool {
        try {
            // AI NOTE: Auth::id() returns null during installation (no user logged in)
            $userId = $userId ?? Auth::id();

            $stmt = $this->db->prepare("
                INSERT INTO changelog (user_id, entity_type, entity_id, entity_name, action, changes, created_at)
                VALUES (:user_id, :entity_type, :entity_id, :entity_name, :action, :changes, NOW())
            ");

            return $stmt->execute([
                'user_id' => $userId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'entity_name' => $entityName,
                'action' => $action,
                // AI NOTE: JSON_UNESCAPED_UNICODE preserves German umlauts (ä, ö, ü, ß)
                'changes' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (Exception $e) {
            // AI NOTE: Never let changelog errors break main operations
            Logger::error('ChangelogService: Failed to log change', [
                'error' => $e->getMessage(),
                'entity_type' => $entityType,
                'entity_id' => $entityId
            ]);
            return false;
        }
    }

    /**
     * Convenience method: Log entity creation
     *
     * USAGE:
     * ```php
     * $changelog->logCreate('game', $id, $game['name'], ['difficulty' => 1]);
     * ```
     *
     * @param string $entityType Entity type
     * @param int $entityId Entity primary key
     * @param string $entityName Entity display name
     * @param array $data Initial data (optional, for auditing)
     * @return bool Success status
     */
    public function logCreate(string $entityType, int $entityId, string $entityName, array $data = []): bool
    {
        return $this->log($entityType, $entityId, $entityName, 'create', $data);
    }

    /**
     * Convenience method: Log entity update
     *
     * AI NOTE: Returns true immediately if no changes detected (optimization).
     *
     * USAGE:
     * ```php
     * $changes = $changelog->getChanges($oldData, $newData, ['name', 'description']);
     * $changelog->logUpdate('game', $id, $game['name'], $changes);
     * ```
     *
     * @param string $entityType Entity type
     * @param int $entityId Entity primary key
     * @param string $entityName Entity display name
     * @param array $changes Change data from getChanges()
     * @return bool Success status
     */
    public function logUpdate(string $entityType, int $entityId, string $entityName, array $changes = []): bool
    {
        if (empty($changes)) {
            return true; // AI NOTE: No changes = no log entry needed, but return success
        }
        return $this->log($entityType, $entityId, $entityName, 'update', $changes);
    }

    /**
     * Convenience method: Log entity deletion
     *
     * AI NOTE: The entityName is stored so the log remains readable after
     * the original entity is deleted from the database.
     *
     * @param string $entityType Entity type
     * @param int $entityId Entity primary key
     * @param string $entityName Entity display name (preserved after deletion)
     * @param array $data Final state data (optional, for auditing)
     * @return bool Success status
     */
    public function logDelete(string $entityType, int $entityId, string $entityName, array $data = []): bool
    {
        return $this->log($entityType, $entityId, $entityName, 'delete', $data);
    }

    /**
     * Compare old and new data to generate change list
     *
     * AI NOTE: This is the primary change detection method. It compares
     * only the fields specified in $trackFields, ignoring timestamps and
     * other auto-generated fields.
     *
     * USAGE:
     * ```php
     * $trackFields = ['name', 'description', 'difficulty', 'is_active'];
     * $changes = $changelog->getChanges($oldGame, $newGame, $trackFields);
     * // Result: ['name' => ['old' => 'Old Name', 'new' => 'New Name']]
     * ```
     *
     * AI NOTE: Uses string comparison to handle type coercion safely
     * (e.g., integer 1 vs string "1" are considered equal).
     *
     * @param array $old Original entity data
     * @param array $new Updated entity data
     * @param array $trackFields List of field names to compare
     * @return array Changes in format ['field' => ['old' => X, 'new' => Y]]
     */
    public function getChanges(array $old, array $new, array $trackFields): array
    {
        $changes = [];

        foreach ($trackFields as $field) {
            $oldValue = $old[$field] ?? '';
            $newValue = $new[$field] ?? '';

            // AI NOTE: String comparison handles type coercion (int vs string)
            if ((string)$oldValue !== (string)$newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Get recent changelog entries with user information
     *
     * AI NOTE: Joins with users table to get username for display.
     * The 'username' column is aliased to 'user_name' for template compatibility.
     *
     * QUERY DETAILS:
     * - LEFT JOIN ensures entries appear even if user was deleted
     * - Results ordered by most recent first
     * - Supports pagination via limit/offset
     *
     * @param int $limit Maximum entries to return (default 50)
     * @param int $offset Number of entries to skip (default 0)
     * @return array Changelog entries with user_name field
     */
    public function getRecent(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username as user_name
            FROM changelog c
            LEFT JOIN users u ON u.id = c.user_id
            ORDER BY c.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        // AI NOTE: bindValue with PARAM_INT prevents SQL injection and ensures type safety
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get changelog entries for a specific entity
     *
     * AI NOTE: Used on entity detail pages to show history of changes
     * (e.g., "Show history" button on game detail page).
     *
     * @param string $entityType Entity type to filter by
     * @param int $entityId Entity ID to filter by
     * @return array All changelog entries for this entity
     */
    public function getForEntity(string $entityType, int $entityId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username as user_name
            FROM changelog c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE c.entity_type = :entity_type AND c.entity_id = :entity_id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get changelog entries by user
     *
     * AI NOTE: Used for user activity reports or admin monitoring.
     *
     * @param int $userId User ID to filter by
     * @param int $limit Maximum entries to return
     * @return array Changelog entries by this user
     */
    public function getByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username as user_name
            FROM changelog c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE c.user_id = :user_id
            ORDER BY c.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get changelog entries by action type
     *
     * AI NOTE: Used for filtering changelog view by action
     * (e.g., show only deletions for data recovery purposes).
     *
     * @param string $action Action type: 'create', 'update', or 'delete'
     * @param int $limit Maximum entries to return
     * @return array Changelog entries with specified action
     */
    public function getByAction(string $action, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username as user_name
            FROM changelog c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE c.action = :action
            ORDER BY c.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue('action', $action, PDO::PARAM_STR);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get total count of changelog entries
     *
     * AI NOTE: Used for pagination and statistics on dashboard/settings.
     *
     * @param string|null $entityType Optional filter by entity type
     * @return int Total count of entries
     */
    public function getCount(?string $entityType = null): int
    {
        if ($entityType) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM changelog WHERE entity_type = :entity_type");
            $stmt->execute(['entity_type' => $entityType]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM changelog");
        }

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get human-readable German label for action
     *
     * AI NOTE: Used in views to display localized action labels.
     * The application UI is in German.
     *
     * @param string $action Action code
     * @return string German label
     */
    public static function getActionLabel(string $action): string
    {
        return match($action) {
            'create' => 'Erstellt',
            'update' => 'Aktualisiert',
            'delete' => 'Gelöscht',
            default => ucfirst($action),
        };
    }

    /**
     * Get human-readable German label for entity type
     *
     * AI NOTE: Maps internal entity type codes to German display names.
     * Used throughout the changelog view and notifications.
     *
     * @param string $entityType Entity type code
     * @return string German label
     */
    public static function getEntityTypeLabel(string $entityType): string
    {
        return match($entityType) {
            'game' => 'Spiel',
            'box' => 'Box',
            'category' => 'Altersgruppe',
            'tag' => 'Thema',
            'material' => 'Material',
            'group' => 'Gruppe',
            'event' => 'Termin',
            'user' => 'Benutzer',
            default => ucfirst($entityType),
        };
    }

    /**
     * Format JSON changes for display in views
     *
     * AI NOTE: Parses the JSON string stored in database and converts
     * to a structured array suitable for template rendering.
     *
     * OUTPUT FORMAT:
     * ```php
     * [
     *     ['field' => 'Name', 'old' => 'Old Name', 'new' => 'New Name'],
     *     ['field' => 'Beschreibung', 'old' => '...', 'new' => '...'],
     * ]
     * ```
     *
     * @param string $changesJson JSON string from database
     * @return array Formatted changes for display
     */
    public static function formatChanges(string $changesJson): array
    {
        $changes = json_decode($changesJson, true);
        if (!is_array($changes)) {
            return [];
        }

        $formatted = [];
        foreach ($changes as $field => $values) {
            $fieldLabel = self::getFieldLabel($field);

            if (is_array($values) && isset($values['old'], $values['new'])) {
                $formatted[] = [
                    'field' => $fieldLabel,
                    'old' => $values['old'],
                    'new' => $values['new'],
                ];
            } else {
                // AI NOTE: Handle simple value storage (for 'create' action data)
                $formatted[] = [
                    'field' => $fieldLabel,
                    'value' => is_array($values) ? json_encode($values) : $values,
                ];
            }
        }

        return $formatted;
    }

    /**
     * Get human-readable German label for field name
     *
     * AI NOTE: Maps database column names to German display labels.
     * Used by formatChanges() for user-friendly output.
     *
     * @param string $field Database column name
     * @return string German label
     */
    private static function getFieldLabel(string $field): string
    {
        return match($field) {
            'name' => 'Name',
            'description' => 'Beschreibung',
            'color' => 'Farbe',
            'image_path' => 'Bild',
            'location' => 'Standort',
            'label' => 'Etikett',
            'sort_order' => 'Sortierung',
            'min_players' => 'Min. Spieler',
            'max_players' => 'Max. Spieler',
            'duration_minutes' => 'Dauer',
            'instructions' => 'Anleitung',
            'is_outdoor' => 'Outdoor',
            'is_active' => 'Aktiv',
            'box_id' => 'Box',
            'category_id' => 'Altersgruppe',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }

    /**
     * Remove old changelog entries based on retention policy
     *
     * AI NOTE: Called periodically (e.g., via cron or admin action) to
     * prevent unlimited growth of the changelog table.
     *
     * DEFAULT: Keep 365 days of history (approximately 1 year).
     *
     * USAGE:
     * ```php
     * $deletedCount = $changelog->cleanup(90); // Keep only last 90 days
     * ```
     *
     * @param int $keepDays Number of days to retain (default 365)
     * @return int Number of entries deleted
     */
    public function cleanup(int $keepDays = 365): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM changelog
            WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->execute(['days' => $keepDays]);

        return $stmt->rowCount();
    }
}
