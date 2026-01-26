<?php
/**
 * Game Model
 */

class Game extends Model
{
    protected static string $table = 'games';
    protected static array $fillable = [
        'name',
        'description',
        'instructions',
        'min_players',
        'max_players',
        'duration_minutes',
        'difficulty',
        'is_outdoor',
        'is_active',
        'is_favorite',
        'image_path',
        'box_id',
        'category_id',
    ];

    /**
     * Allowed columns for ORDER BY (security whitelist to prevent SQL injection)
     */
    private static array $allowedOrderColumns = [
        'name', 'created_at', 'updated_at', 'duration_minutes', 'difficulty',
        'min_players', 'max_players', 'is_favorite', 'is_outdoor', 'is_active'
    ];

    /**
     * Get all games with related data
     */
    public static function allWithRelations(array $filters = [], string $orderBy = 'name', string $direction = 'ASC'): array
    {
        $db = self::getDb();
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // Security: Validate orderBy against whitelist
        if (!in_array($orderBy, self::$allowedOrderColumns, true)) {
            $orderBy = 'name';
        }

        $where = [];
        $params = [];

        // Apply filters
        if (!empty($filters['box_id'])) {
            $where[] = 'g.box_id = :box_id';
            $params['box_id'] = $filters['box_id'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'g.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['tag_id'])) {
            $where[] = 'g.id IN (SELECT game_id FROM game_tags WHERE tag_id = :tag_id)';
            $params['tag_id'] = $filters['tag_id'];
        }

        if (isset($filters['is_outdoor'])) {
            $where[] = 'g.is_outdoor = :is_outdoor';
            $params['is_outdoor'] = $filters['is_outdoor'];
        }

        if (isset($filters['is_active'])) {
            $where[] = 'g.is_active = :is_active';
            $params['is_active'] = $filters['is_active'];
        }

        if (isset($filters['is_favorite']) && $filters['is_favorite'] !== null) {
            $where[] = 'g.is_favorite = :is_favorite';
            $params['is_favorite'] = $filters['is_favorite'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(g.name LIKE :search OR g.description LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT g.*,
                       b.name as box_name, b.label as box_label,
                       c.name as category_name
                FROM games g
                LEFT JOIN boxes b ON b.id = g.box_id
                LEFT JOIN categories c ON c.id = g.category_id
                {$whereClause}
                ORDER BY g.{$orderBy} {$direction}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Find a game with all related data
     */
    public static function findWithRelations(int $id): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT g.*,
                   b.name as box_name, b.label as box_label, b.location as box_location,
                   c.name as category_name
            FROM games g
            LEFT JOIN boxes b ON b.id = g.box_id
            LEFT JOIN categories c ON c.id = g.category_id
            WHERE g.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $game = $stmt->fetch();

        if (!$game) {
            return null;
        }

        // Get tags
        $game['tags'] = self::getTags($id);

        // Get materials
        $game['materials'] = self::getMaterials($id);

        return $game;
    }

    /**
     * Get tags for a game
     */
    public static function getTags(int $gameId): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT t.* FROM tags t
            INNER JOIN game_tags gt ON gt.tag_id = t.id
            WHERE gt.game_id = :game_id
            ORDER BY t.name ASC
        ");
        $stmt->execute(['game_id' => $gameId]);

        return $stmt->fetchAll();
    }

    /**
     * Get materials for a game
     */
    public static function getMaterials(int $gameId): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT m.*, gm.quantity FROM materials m
            INNER JOIN game_materials gm ON gm.material_id = m.id
            WHERE gm.game_id = :game_id
            ORDER BY m.name ASC
        ");
        $stmt->execute(['game_id' => $gameId]);

        return $stmt->fetchAll();
    }

    /**
     * Update game tags
     */
    public static function updateTags(int $gameId, array $tagIds): void
    {
        $db = self::getDb();

        // Delete existing tags
        $stmt = $db->prepare("DELETE FROM game_tags WHERE game_id = :game_id");
        $stmt->execute(['game_id' => $gameId]);

        // Insert new tags
        if (!empty($tagIds)) {
            $stmt = $db->prepare("INSERT INTO game_tags (game_id, tag_id) VALUES (:game_id, :tag_id)");
            foreach ($tagIds as $tagId) {
                $stmt->execute(['game_id' => $gameId, 'tag_id' => $tagId]);
            }
        }
    }

    /**
     * Update game materials
     */
    public static function updateMaterials(int $gameId, array $materials): void
    {
        $db = self::getDb();

        // Delete existing materials
        $stmt = $db->prepare("DELETE FROM game_materials WHERE game_id = :game_id");
        $stmt->execute(['game_id' => $gameId]);

        // Insert new materials
        if (!empty($materials)) {
            $stmt = $db->prepare("INSERT INTO game_materials (game_id, material_id, quantity) VALUES (:game_id, :material_id, :quantity)");
            foreach ($materials as $material) {
                $stmt->execute([
                    'game_id' => $gameId,
                    'material_id' => $material['id'],
                    'quantity' => $material['quantity'] ?? 1,
                ]);
            }
        }
    }

    /**
     * Check if game name exists
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        return self::valueExists('name', $name, $excludeId);
    }

    /**
     * Search games
     */
    public static function search(string $query, int $limit = 20): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT id, name, image_path FROM games
            WHERE name LIKE :query OR description LIKE :query
            ORDER BY name ASC
            LIMIT :limit
        ");
        $stmt->bindValue('query', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get a random game
     */
    public static function random(array $filters = []): ?array
    {
        $db = self::getDb();

        $where = ['g.is_active = 1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'g.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['tag_id'])) {
            $where[] = 'g.id IN (SELECT game_id FROM game_tags WHERE tag_id = :tag_id)';
            $params['tag_id'] = $filters['tag_id'];
        }

        if (isset($filters['is_outdoor'])) {
            $where[] = 'g.is_outdoor = :is_outdoor';
            $params['is_outdoor'] = $filters['is_outdoor'];
        }

        if (!empty($filters['max_players'])) {
            $where[] = '(g.min_players IS NULL OR g.min_players <= :max_players)';
            $params['max_players'] = $filters['max_players'];
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT g.*, b.name as box_name, b.label as box_label
                FROM games g
                LEFT JOIN boxes b ON b.id = g.box_id
                {$whereClause}
                ORDER BY RAND()
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get games count by category
     */
    public static function countByCategory(): array
    {
        $db = self::getDb();

        $stmt = $db->query("
            SELECT c.id, c.name, COUNT(g.id) as game_count
            FROM categories c
            LEFT JOIN games g ON g.category_id = c.id AND g.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Get games count by tag
     */
    public static function countByTag(): array
    {
        $db = self::getDb();

        $stmt = $db->query("
            SELECT t.id, t.name, t.color, COUNT(gt.game_id) as game_count
            FROM tags t
            LEFT JOIN game_tags gt ON gt.tag_id = t.id
            LEFT JOIN games g ON g.id = gt.game_id AND g.is_active = 1
            GROUP BY t.id
            ORDER BY t.name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Get games count by box
     */
    public static function countByBox(): array
    {
        $db = self::getDb();

        $stmt = $db->query("
            SELECT b.id, b.name, b.label, COUNT(g.id) as game_count
            FROM boxes b
            LEFT JOIN games g ON g.box_id = b.id AND g.is_active = 1
            GROUP BY b.id
            ORDER BY b.name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Get total games count
     */
    public static function getStats(): array
    {
        $db = self::getDb();

        $stmt = $db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_outdoor = 1 THEN 1 ELSE 0 END) as outdoor
            FROM games
        ");

        return $stmt->fetch() ?: ['total' => 0, 'active' => 0, 'outdoor' => 0];
    }

    /**
     * Get recently added games
     */
    public static function getRecent(int $limit = 5): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT g.*, b.name as box_name, c.name as category_name
            FROM games g
            LEFT JOIN boxes b ON b.id = g.box_id
            LEFT JOIN categories c ON c.id = g.category_id
            WHERE g.is_active = 1
            ORDER BY g.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Fulltext search
     */
    public static function fulltextSearch(string $query, int $limit = 50): array
    {
        $db = self::getDb();

        // Try fulltext search first
        try {
            $stmt = $db->prepare("
                SELECT g.*, b.name as box_name, c.name as category_name,
                       MATCH(g.name, g.description, g.instructions) AGAINST(:query IN NATURAL LANGUAGE MODE) as relevance
                FROM games g
                LEFT JOIN boxes b ON b.id = g.box_id
                LEFT JOIN categories c ON c.id = g.category_id
                WHERE MATCH(g.name, g.description, g.instructions) AGAINST(:query IN NATURAL LANGUAGE MODE)
                ORDER BY relevance DESC
                LIMIT :limit
            ");
            $stmt->bindValue('query', $query, PDO::PARAM_STR);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Fallback to LIKE search if fulltext not available
            return self::search($query, $limit);
        }
    }

    /**
     * Get all games for select dropdown
     */
    public static function getForSelect(): array
    {
        $db = self::getDb();

        $stmt = $db->query("SELECT id, name FROM games WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Toggle favorite status
     */
    public static function toggleFavorite(int $id): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("UPDATE games SET is_favorite = NOT is_favorite WHERE id = :id");
        $stmt->execute(['id' => $id]);

        // Return the new favorite status
        $stmt = $db->prepare("SELECT is_favorite FROM games WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ? (bool)$result['is_favorite'] : false;
    }

    /**
     * Get all favorite games
     */
    public static function getFavorites(int $limit = 8): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT g.*, b.name as box_name, c.name as category_name
            FROM games g
            LEFT JOIN boxes b ON b.id = g.box_id
            LEFT JOIN categories c ON c.id = g.category_id
            WHERE g.is_favorite = 1 AND g.is_active = 1
            ORDER BY g.name ASC
            LIMIT :limit
        ");
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Duplicate a game (transactional)
     */
    public static function duplicate(int $id): ?int
    {
        $db = self::getDb();

        $game = self::findWithRelations($id);
        if (!$game) {
            return null;
        }

        try {
            $db->beginTransaction();

            // Generate a new name with limit on attempts
            $newName = $game['name'] . ' (Kopie)';
            $counter = 1;
            $maxAttempts = 100;
            while (self::nameExists($newName) && $counter < $maxAttempts) {
                $counter++;
                $newName = $game['name'] . ' (Kopie ' . $counter . ')';
            }

            if ($counter >= $maxAttempts) {
                $db->rollBack();
                Logger::error('Failed to generate unique name for game duplicate', ['id' => $id]);
                return null;
            }

            // Create new game directly in transaction
            $stmt = $db->prepare("
                INSERT INTO games (name, description, instructions, min_players, max_players,
                                   duration_minutes, is_outdoor, is_active, image_path, box_id, category_id)
                VALUES (:name, :description, :instructions, :min_players, :max_players,
                        :duration_minutes, :is_outdoor, :is_active, :image_path, :box_id, :category_id)
            ");
            $stmt->execute([
                'name' => $newName,
                'description' => $game['description'],
                'instructions' => $game['instructions'],
                'min_players' => $game['min_players'],
                'max_players' => $game['max_players'],
                'duration_minutes' => $game['duration_minutes'],
                'is_outdoor' => $game['is_outdoor'],
                'is_active' => $game['is_active'],
                'image_path' => null, // Don't copy image
                'box_id' => $game['box_id'],
                'category_id' => $game['category_id'],
            ]);
            $newGameId = (int)$db->lastInsertId();

            if (!$newGameId) {
                $db->rollBack();
                return null;
            }

            // Copy tags
            if (!empty($game['tags'])) {
                $tagStmt = $db->prepare("INSERT INTO game_tags (game_id, tag_id) VALUES (:game_id, :tag_id)");
                foreach ($game['tags'] as $tag) {
                    $tagStmt->execute(['game_id' => $newGameId, 'tag_id' => $tag['id']]);
                }
            }

            // Copy materials
            if (!empty($game['materials'])) {
                $materialStmt = $db->prepare("INSERT INTO game_materials (game_id, material_id, quantity) VALUES (:game_id, :material_id, :quantity)");
                foreach ($game['materials'] as $material) {
                    $materialStmt->execute([
                        'game_id' => $newGameId,
                        'material_id' => $material['id'],
                        'quantity' => $material['quantity'] ?? 1
                    ]);
                }
            }

            $db->commit();
            return $newGameId;
        } catch (PDOException $e) {
            $db->rollBack();
            Logger::error('Failed to duplicate game', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
