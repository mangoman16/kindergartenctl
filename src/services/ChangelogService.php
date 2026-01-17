<?php
/**
 * Changelog Service
 *
 * Handles logging of all entity changes for audit trail.
 */

class ChangelogService
{
    private static ?ChangelogService $instance = null;
    private PDO $db;

    private function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): ChangelogService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log a change
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
                'changes' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (Exception $e) {
            Logger::error('ChangelogService: Failed to log change', [
                'error' => $e->getMessage(),
                'entity_type' => $entityType,
                'entity_id' => $entityId
            ]);
            return false;
        }
    }

    /**
     * Log a create action
     */
    public function logCreate(string $entityType, int $entityId, string $entityName, array $data = []): bool
    {
        return $this->log($entityType, $entityId, $entityName, 'create', $data);
    }

    /**
     * Log an update action
     */
    public function logUpdate(string $entityType, int $entityId, string $entityName, array $changes = []): bool
    {
        if (empty($changes)) {
            return true; // Nothing to log
        }
        return $this->log($entityType, $entityId, $entityName, 'update', $changes);
    }

    /**
     * Log a delete action
     */
    public function logDelete(string $entityType, int $entityId, string $entityName, array $data = []): bool
    {
        return $this->log($entityType, $entityId, $entityName, 'delete', $data);
    }

    /**
     * Get changes between old and new data
     */
    public function getChanges(array $old, array $new, array $trackFields): array
    {
        $changes = [];

        foreach ($trackFields as $field) {
            $oldValue = $old[$field] ?? '';
            $newValue = $new[$field] ?? '';

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
     * Get recent changelog entries
     */
    public function getRecent(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name as user_name
            FROM changelog c
            LEFT JOIN users u ON u.id = c.user_id
            ORDER BY c.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get changelog entries for a specific entity
     */
    public function getForEntity(string $entityType, int $entityId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name as user_name
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
     */
    public function getByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name as user_name
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
     * Get changelog entries filtered by action type
     */
    public function getByAction(string $action, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name as user_name
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
     * Get action label in German
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
     * Get entity type label in German
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
     * Format changes for display
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
                $formatted[] = [
                    'field' => $fieldLabel,
                    'value' => is_array($values) ? json_encode($values) : $values,
                ];
            }
        }

        return $formatted;
    }

    /**
     * Get field label in German
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
     * Clean up old changelog entries (keep last N days)
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
