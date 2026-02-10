<?php
/**
 * =====================================================================================
 * GROUP MODEL - Collections of Games and Materials
 * =====================================================================================
 *
 * Virtual collections for organizing games/materials for activities.
 * Uses SELECT FOR UPDATE in addGame()/addMaterial() for race condition safety.
 * addItem()/removeItem() dispatch polymorphically with strict in_array.
 *
 * @package KindergartenOrganizer\Models
 * =====================================================================================
 *
 * Group Model
 *
 * Groups are collections of games and materials for specific activities.
 */

class Group extends Model
{
    protected static string $table = 'groups';
    protected static array $fillable = [
        'name',
        'description',
        'image_path',
    ];

    /**
     * Get all groups with item counts
     */
    public static function allWithCounts(): array
    {
        $db = self::getDb();

        $stmt = $db->query("
            SELECT g.*,
                   (SELECT COUNT(*) FROM group_games gg WHERE gg.group_id = g.id) as game_count,
                   (SELECT COUNT(*) FROM group_materials gm WHERE gm.group_id = g.id) as material_count
            FROM groups g
            ORDER BY g.name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Find group with item counts
     */
    public static function findWithCounts(int $id): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT g.*,
                   (SELECT COUNT(*) FROM group_games gg WHERE gg.group_id = g.id) as game_count,
                   (SELECT COUNT(*) FROM group_materials gm WHERE gm.group_id = g.id) as material_count
            FROM groups g
            WHERE g.id = :id
        ");
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get games in a group
     */
    public static function getGames(int $groupId): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT ga.*, b.name as box_name, b.label as box_label, gg.sort_order
            FROM games ga
            INNER JOIN group_games gg ON gg.game_id = ga.id
            LEFT JOIN boxes b ON b.id = ga.box_id
            WHERE gg.group_id = :group_id
            ORDER BY gg.sort_order ASC, ga.name ASC
        ");
        $stmt->execute(['group_id' => $groupId]);

        return $stmt->fetchAll();
    }

    /**
     * Get materials in a group
     */
    public static function getMaterials(int $groupId): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT m.*, gm.quantity, gm.sort_order, b.name as box_name
            FROM materials m
            INNER JOIN group_materials gm ON gm.material_id = m.id
            LEFT JOIN boxes b ON b.id = m.box_id
            WHERE gm.group_id = :group_id
            ORDER BY gm.sort_order ASC, m.name ASC
        ");
        $stmt->execute(['group_id' => $groupId]);

        return $stmt->fetchAll();
    }

    /**
     * Add a game to the group
     */
    public static function addGame(int $groupId, int $gameId): bool
    {
        $db = self::getDb();

        try {
            $db->beginTransaction();

            // Check if already exists using SELECT FOR UPDATE (row-level locking)
            $stmt = $db->prepare("SELECT id FROM group_games WHERE group_id = :group_id AND game_id = :game_id FOR UPDATE");
            $stmt->execute(['group_id' => $groupId, 'game_id' => $gameId]);
            if ($stmt->fetch()) {
                $db->rollBack();
                return false; // Already exists
            }

            // Get next sort order with safe fallback
            $stmt = $db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM group_games WHERE group_id = :group_id");
            $stmt->execute(['group_id' => $groupId]);
            $result = $stmt->fetch();
            $nextOrder = $result ? (int)$result['next_order'] : 1;

            // Insert
            $stmt = $db->prepare("INSERT INTO group_games (group_id, game_id, sort_order) VALUES (:group_id, :game_id, :sort_order)");
            $success = $stmt->execute(['group_id' => $groupId, 'game_id' => $gameId, 'sort_order' => $nextOrder]);

            $db->commit();
            return $success;
        } catch (PDOException $e) {
            $db->rollBack();
            Logger::error('Failed to add game to group', [
                'group_id' => $groupId,
                'game_id' => $gameId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Remove a game from the group
     */
    public static function removeGame(int $groupId, int $gameId): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("DELETE FROM group_games WHERE group_id = :group_id AND game_id = :game_id");
        return $stmt->execute(['group_id' => $groupId, 'game_id' => $gameId]);
    }

    /**
     * Add a material to the group
     */
    public static function addMaterial(int $groupId, int $materialId, int $quantity = 1): bool
    {
        $db = self::getDb();

        // Validate quantity
        $quantity = max(1, (int)$quantity);

        try {
            $db->beginTransaction();

            // Check if already exists with row locking
            $stmt = $db->prepare("SELECT id FROM group_materials WHERE group_id = :group_id AND material_id = :material_id FOR UPDATE");
            $stmt->execute(['group_id' => $groupId, 'material_id' => $materialId]);
            if ($stmt->fetch()) {
                // Update quantity instead
                $stmt = $db->prepare("UPDATE group_materials SET quantity = quantity + :quantity WHERE group_id = :group_id AND material_id = :material_id");
                $success = $stmt->execute(['group_id' => $groupId, 'material_id' => $materialId, 'quantity' => $quantity]);
                $db->commit();
                return $success;
            }

            // Get next sort order with safe fallback
            $stmt = $db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM group_materials WHERE group_id = :group_id");
            $stmt->execute(['group_id' => $groupId]);
            $result = $stmt->fetch();
            $nextOrder = $result ? (int)$result['next_order'] : 1;

            // Insert
            $stmt = $db->prepare("INSERT INTO group_materials (group_id, material_id, quantity, sort_order) VALUES (:group_id, :material_id, :quantity, :sort_order)");
            $success = $stmt->execute(['group_id' => $groupId, 'material_id' => $materialId, 'quantity' => $quantity, 'sort_order' => $nextOrder]);

            $db->commit();
            return $success;
        } catch (PDOException $e) {
            $db->rollBack();
            Logger::error('Failed to add material to group', [
                'group_id' => $groupId,
                'material_id' => $materialId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Remove a material from the group
     */
    public static function removeMaterial(int $groupId, int $materialId): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("DELETE FROM group_materials WHERE group_id = :group_id AND material_id = :material_id");
        return $stmt->execute(['group_id' => $groupId, 'material_id' => $materialId]);
    }

    /**
     * Update games in a group
     */
    public static function updateGames(int $groupId, array $gameIds): void
    {
        $db = self::getDb();

        try {
            $db->beginTransaction();

            // Delete existing
            $stmt = $db->prepare("DELETE FROM group_games WHERE group_id = :group_id");
            $stmt->execute(['group_id' => $groupId]);

            // Insert new with validation
            $stmt = $db->prepare("INSERT INTO group_games (group_id, game_id, sort_order) VALUES (:group_id, :game_id, :sort_order)");
            foreach ($gameIds as $order => $gameId) {
                // Validate gameId is numeric
                if (!is_numeric($gameId)) {
                    continue;
                }
                $stmt->execute(['group_id' => $groupId, 'game_id' => (int)$gameId, 'sort_order' => (int)$order]);
            }

            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            Logger::error('Failed to update group games', [
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException('Fehler beim Aktualisieren der Spiele');
        }
    }

    /**
     * Update materials in a group
     */
    public static function updateMaterials(int $groupId, array $materials): void
    {
        $db = self::getDb();

        try {
            $db->beginTransaction();

            // Delete existing
            $stmt = $db->prepare("DELETE FROM group_materials WHERE group_id = :group_id");
            $stmt->execute(['group_id' => $groupId]);

            // Insert new with validation
            $stmt = $db->prepare("INSERT INTO group_materials (group_id, material_id, quantity, sort_order) VALUES (:group_id, :material_id, :quantity, :sort_order)");
            foreach ($materials as $order => $material) {
                // Skip invalid entries
                if (!is_array($material) || !isset($material['id'])) {
                    continue;
                }
                $stmt->execute([
                    'group_id' => $groupId,
                    'material_id' => (int)$material['id'],
                    'quantity' => max(1, (int)($material['quantity'] ?? 1)),
                    'sort_order' => (int)$order
                ]);
            }

            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            Logger::error('Failed to update group materials', [
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException('Fehler beim Aktualisieren der Materialien');
        }
    }

    /**
     * Add an item to a group (polymorphic)
     */
    public static function addItem(int $groupId, string $itemType, int $itemId): bool
    {
        if (!in_array($itemType, ['game', 'material'], true)) {
            return false;
        }

        if ($itemType === 'game') {
            return self::addGame($groupId, $itemId);
        } else {
            return self::addMaterial($groupId, $itemId);
        }
    }

    /**
     * Remove an item from a group (polymorphic)
     */
    public static function removeItem(int $groupId, string $itemType, int $itemId): bool
    {
        if (!in_array($itemType, ['game', 'material'], true)) {
            return false;
        }

        if ($itemType === 'game') {
            return self::removeGame($groupId, $itemId);
        } else {
            return self::removeMaterial($groupId, $itemId);
        }
    }

    /**
     * Check if group name exists
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        return self::valueExists('name', $name, $excludeId);
    }

    /**
     * Get groups for select dropdown
     */
    public static function getForSelect(): array
    {
        $db = self::getDb();

        $stmt = $db->query("SELECT id, name FROM groups ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Duplicate a group with all its items (transactional)
     */
    public static function duplicate(int $id): ?int
    {
        $db = self::getDb();

        $group = self::findWithCounts($id);
        if (!$group) {
            return null;
        }

        try {
            $db->beginTransaction();

            // Generate a new name with limit on attempts
            $newName = $group['name'] . ' (Kopie)';
            $counter = 1;
            $maxAttempts = 100;
            while (self::nameExists($newName) && $counter < $maxAttempts) {
                $counter++;
                $newName = $group['name'] . ' (Kopie ' . $counter . ')';
            }

            if ($counter >= $maxAttempts) {
                $db->rollBack();
                Logger::error('Failed to generate unique name for group duplicate', ['id' => $id]);
                return null;
            }

            // Create new group directly in transaction
            $stmt = $db->prepare("INSERT INTO `groups` (name, description, image_path) VALUES (:name, :description, :image_path)");
            $stmt->execute([
                'name' => $newName,
                'description' => $group['description'],
                'image_path' => null, // Don't copy image
            ]);
            $newGroupId = (int)$db->lastInsertId();

            if (!$newGroupId) {
                $db->rollBack();
                return null;
            }

            // Copy games
            $games = self::getGames($id);
            if (!empty($games)) {
                $insertStmt = $db->prepare("INSERT INTO group_games (group_id, game_id, sort_order) VALUES (:group_id, :game_id, :sort_order)");
                foreach ($games as $index => $game) {
                    $insertStmt->execute([
                        'group_id' => $newGroupId,
                        'game_id' => $game['id'],
                        'sort_order' => $game['sort_order'] ?? $index
                    ]);
                }
            }

            // Copy materials
            $materials = self::getMaterials($id);
            if (!empty($materials)) {
                $insertStmt = $db->prepare("INSERT INTO group_materials (group_id, material_id, quantity, sort_order) VALUES (:group_id, :material_id, :quantity, :sort_order)");
                foreach ($materials as $index => $material) {
                    $insertStmt->execute([
                        'group_id' => $newGroupId,
                        'material_id' => $material['id'],
                        'quantity' => $material['quantity'] ?? 1,
                        'sort_order' => $material['sort_order'] ?? $index
                    ]);
                }
            }

            $db->commit();
            return $newGroupId;
        } catch (PDOException $e) {
            $db->rollBack();
            Logger::error('Failed to duplicate group', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
