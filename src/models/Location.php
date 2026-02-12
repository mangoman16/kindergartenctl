<?php
/**
 * Location Model - Predefined places where boxes are stored (Standorte)
 */

class Location extends Model
{
    protected static string $table = 'locations';
    protected static array $fillable = [
        'name',
        'description',
    ];

    /**
     * Allowed columns for ordering
     */
    private static array $allowedOrderColumns = ['name', 'created_at', 'updated_at'];

    /**
     * Get all locations with box count
     */
    public static function allWithBoxCount(string $orderBy = 'name', string $direction = 'ASC'): array
    {
        $db = self::getDb();
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        if (!in_array($orderBy, self::$allowedOrderColumns, true)) {
            $orderBy = 'name';
        }

        $sql = "SELECT l.*, COUNT(b.id) as box_count
                FROM locations l
                LEFT JOIN boxes b ON b.location_id = l.id
                GROUP BY l.id
                ORDER BY l.{$orderBy} {$direction}";

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get location with box count
     */
    public static function findWithBoxCount(int $id): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT l.*, COUNT(b.id) as box_count
            FROM locations l
            LEFT JOIN boxes b ON b.location_id = l.id
            WHERE l.id = :id
            GROUP BY l.id
        ");
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get boxes at this location
     */
    public static function getBoxes(int $locationId): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT b.*, COUNT(m.id) as material_count
            FROM boxes b
            LEFT JOIN materials m ON m.box_id = b.id
            WHERE b.location_id = :location_id
            GROUP BY b.id
            ORDER BY b.name ASC
        ");
        $stmt->execute(['location_id' => $locationId]);

        return $stmt->fetchAll();
    }

    /**
     * Get locations for select dropdown
     */
    public static function getForSelect(): array
    {
        $db = self::getDb();

        $stmt = $db->query("SELECT id, name FROM locations ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Check if location name exists
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        return self::valueExists('name', $name, $excludeId);
    }

    /**
     * Search locations
     */
    public static function searchLocations(string $query, int $limit = 50): array
    {
        return self::search($query, ['name', 'description'], $limit);
    }
}
