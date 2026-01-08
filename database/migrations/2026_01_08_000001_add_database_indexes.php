<?php
/**
 * Migration: add_database_indexes
 * Created: 2026_01_08_000001
 *
 * Adds additional indexes for query optimization on large datasets.
 */

class AddDatabaseIndexes
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        // Add composite index on games for common filter combinations
        $this->addIndexIfNotExists('games', 'idx_games_filters', '(is_active, is_favorite, is_outdoor)');

        // Add index on games created_at for sorting
        $this->addIndexIfNotExists('games', 'idx_games_created', '(created_at DESC)');

        // Add index on materials for box lookup
        $this->addIndexIfNotExists('materials', 'idx_materials_box', '(box_id, name)');

        // Add index on materials for favorite filter
        $this->addIndexIfNotExists('materials', 'idx_materials_favorite', '(is_favorite)');

        // Add index on calendar_events for date range queries
        $this->addIndexIfNotExists('calendar_events', 'idx_events_date_type', '(event_date, event_type)');

        // Add index on changelog for filtering
        $this->addIndexIfNotExists('changelog', 'idx_changelog_filters', '(entity_type, action, created_at)');

        // Add index on group_games for sort order
        $this->addIndexIfNotExists('group_games', 'idx_group_games_order', '(group_id, sort_order)');

        // Add index on group_materials for sort order
        $this->addIndexIfNotExists('group_materials', 'idx_group_materials_order', '(group_id, sort_order)');
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->dropIndexIfExists('games', 'idx_games_filters');
        $this->dropIndexIfExists('games', 'idx_games_created');
        $this->dropIndexIfExists('materials', 'idx_materials_box');
        $this->dropIndexIfExists('materials', 'idx_materials_favorite');
        $this->dropIndexIfExists('calendar_events', 'idx_events_date_type');
        $this->dropIndexIfExists('changelog', 'idx_changelog_filters');
        $this->dropIndexIfExists('group_games', 'idx_group_games_order');
        $this->dropIndexIfExists('group_materials', 'idx_group_materials_order');
    }

    /**
     * Add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, string $indexName, string $columns): void
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name = :table
            AND index_name = :index_name
        ");
        $stmt->execute(['table' => $table, 'index_name' => $indexName]);

        if ($stmt->fetchColumn() == 0) {
            $this->db->exec("CREATE INDEX {$indexName} ON {$table} {$columns}");
        }
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name = :table
            AND index_name = :index_name
        ");
        $stmt->execute(['table' => $table, 'index_name' => $indexName]);

        if ($stmt->fetchColumn() > 0) {
            $this->db->exec("DROP INDEX {$indexName} ON {$table}");
        }
    }
}
