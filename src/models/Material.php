<?php
/**
 * Material Model
 */

class Material extends Model
{
    protected static string $table = 'materials';
    protected static array $fillable = [
        'name',
        'description',
        'image_path',
        'quantity',
        'is_consumable',
    ];

    /**
     * Get all materials with game count
     */
    public static function allWithGameCount(string $orderBy = 'name', string $direction = 'ASC', array $filters = []): array
    {
        $db = self::getDb();
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $where = [];
        $params = [];

        if (isset($filters['is_favorite']) && $filters['is_favorite'] !== null) {
            $where[] = 'm.is_favorite = :is_favorite';
            $params['is_favorite'] = $filters['is_favorite'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(m.name LIKE :search OR m.description LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT m.*, COUNT(gm.game_id) as game_count
                FROM materials m
                LEFT JOIN game_materials gm ON gm.material_id = m.id
                {$whereClause}
                GROUP BY m.id
                ORDER BY m.{$orderBy} {$direction}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get material with game count
     */
    public static function findWithGameCount(int $id): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT m.*, COUNT(gm.game_id) as game_count
            FROM materials m
            LEFT JOIN game_materials gm ON gm.material_id = m.id
            WHERE m.id = :id
            GROUP BY m.id
        ");
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get games using this material
     */
    public static function getGames(int $materialId, int $limit = 0): array
    {
        $db = self::getDb();

        $sql = "SELECT g.*, gm.quantity as material_quantity
                FROM games g
                INNER JOIN game_materials gm ON gm.game_id = g.id
                WHERE gm.material_id = :material_id
                ORDER BY g.name ASC";

        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute(['material_id' => $materialId]);

        return $stmt->fetchAll();
    }

    /**
     * Get materials for select dropdown
     */
    public static function getForSelect(): array
    {
        $db = self::getDb();

        $stmt = $db->query("SELECT id, name FROM materials ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Check if material name exists
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        return self::valueExists('name', $name, $excludeId);
    }

    /**
     * Quick create a material (for inline creation in game form)
     */
    public static function quickCreate(string $name): ?int
    {
        if (self::nameExists($name)) {
            return null;
        }

        return self::create([
            'name' => trim($name),
        ]);
    }

    /**
     * Search materials by name
     */
    public static function searchByName(string $query, int $limit = 10): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT id, name FROM materials
            WHERE name LIKE :query
            ORDER BY name ASC
            LIMIT {$limit}
        ");
        $stmt->execute(['query' => '%' . $query . '%']);

        return $stmt->fetchAll();
    }

    /**
     * Update material quantities based on game materials
     */
    public static function updateQuantityFromGames(int $materialId): void
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT SUM(quantity) as total
            FROM game_materials
            WHERE material_id = :material_id
        ");
        $stmt->execute(['material_id' => $materialId]);
        $result = $stmt->fetch();

        $total = (int)($result['total'] ?? 0);

        $updateStmt = $db->prepare("
            UPDATE materials SET quantity = :quantity WHERE id = :id
        ");
        $updateStmt->execute(['quantity' => $total, 'id' => $materialId]);
    }

    /**
     * Get consumable materials
     */
    public static function getConsumables(): array
    {
        $db = self::getDb();

        $stmt = $db->query("
            SELECT * FROM materials
            WHERE is_consumable = 1
            ORDER BY name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Get non-consumable materials (equipment)
     */
    public static function getEquipment(): array
    {
        $db = self::getDb();

        $stmt = $db->query("
            SELECT * FROM materials
            WHERE is_consumable = 0
            ORDER BY name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Toggle favorite status
     */
    public static function toggleFavorite(int $id): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("UPDATE materials SET is_favorite = NOT is_favorite WHERE id = :id");
        $stmt->execute(['id' => $id]);

        // Return the new favorite status
        $stmt = $db->prepare("SELECT is_favorite FROM materials WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ? (bool)$result['is_favorite'] : false;
    }

    /**
     * Get all favorite materials
     */
    public static function getFavorites(int $limit = 8): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT m.*, COUNT(gm.game_id) as game_count
            FROM materials m
            LEFT JOIN game_materials gm ON gm.material_id = m.id
            WHERE m.is_favorite = 1
            GROUP BY m.id
            ORDER BY m.name ASC
            LIMIT :limit
        ");
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
