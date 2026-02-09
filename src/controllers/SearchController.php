<?php
/**
 * =====================================================================================
 * SEARCH CONTROLLER - Global Full-Text Search Across All Entities
 * =====================================================================================
 *
 * PURPOSE:
 * Provides a unified search page that queries across games, materials, boxes,
 * tags, and groups. Results are grouped by entity type with tab navigation.
 *
 * AI NOTES:
 * - Uses distinct PDO params (:query1, :query2, etc.) because EMULATE_PREPARES=false
 * - getCounts() re-runs the same queries to get totals per type (not optimal but simple)
 * - Games search delegates to Game::allWithRelations() which handles its own params
 * - Tags search delegates to Tag::searchByName() which handles its own params
 * - Materials, boxes, and groups use inline queries in this controller
 *
 * @package KindergartenOrganizer\Controllers
 * @since 1.0.0
 * =====================================================================================
 */

class SearchController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * Search page
     */
    public function index(): void
    {
        $query = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? 'all';
        $results = [];
        $counts = [];

        if (!empty($query)) {
            $results = $this->performSearch($query, $type);
            $counts = $this->getCounts($query);
        }

        $this->setTitle(__('search.title'));
        $this->addBreadcrumb(__('search.title'));

        $this->render('search/index', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'counts' => $counts,
        ]);
    }

    /**
     * Perform search across entities
     */
    private function performSearch(string $query, string $type): array
    {
        $results = [];

        if ($type === 'all' || $type === 'games') {
            require_once SRC_PATH . '/models/Game.php';
            $results['games'] = Game::allWithRelations(['search' => $query], 'name', 'ASC');
        }

        if ($type === 'all' || $type === 'materials') {
            require_once SRC_PATH . '/models/Material.php';
            $results['materials'] = $this->searchMaterials($query);
        }

        if ($type === 'all' || $type === 'boxes') {
            require_once SRC_PATH . '/models/Box.php';
            $results['boxes'] = $this->searchBoxes($query);
        }

        if ($type === 'all' || $type === 'tags') {
            require_once SRC_PATH . '/models/Tag.php';
            $results['tags'] = Tag::searchByName($query, 50);
        }

        if ($type === 'all' || $type === 'groups') {
            require_once SRC_PATH . '/models/Group.php';
            $results['groups'] = $this->searchGroups($query);
        }

        return $results;
    }

    /**
     * Get counts for each entity type
     */
    private function getCounts(string $query): array
    {
        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/models/Material.php';
        require_once SRC_PATH . '/models/Box.php';
        require_once SRC_PATH . '/models/Tag.php';
        require_once SRC_PATH . '/models/Group.php';

        return [
            'games' => count(Game::allWithRelations(['search' => $query], 'name', 'ASC')),
            'materials' => count($this->searchMaterials($query)),
            'boxes' => count($this->searchBoxes($query)),
            'tags' => count(Tag::searchByName($query, 100)),
            'groups' => count($this->searchGroups($query)),
        ];
    }

    /**
     * Search materials by name or description
     */
    private function searchMaterials(string $query): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM materials
            WHERE name LIKE :query1 OR description LIKE :query2
            ORDER BY name ASC
            LIMIT 50
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute(['query1' => $searchTerm, 'query2' => $searchTerm]);
        return $stmt->fetchAll();
    }

    /**
     * Search boxes by name, label, or location
     *
     * AI NOTE: Boxes contain materials (via materials.box_id), not games directly.
     * The material_count shows how many materials are stored in each box.
     */
    private function searchBoxes(string $query): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT b.*, COUNT(m.id) as material_count
            FROM boxes b
            LEFT JOIN materials m ON m.box_id = b.id
            WHERE b.name LIKE :query1 OR b.label LIKE :query2 OR b.location LIKE :query3
            GROUP BY b.id
            ORDER BY b.name ASC
            LIMIT 50
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute(['query1' => $searchTerm, 'query2' => $searchTerm, 'query3' => $searchTerm]);
        return $stmt->fetchAll();
    }

    /**
     * Search groups by name or description
     */
    private function searchGroups(string $query): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT g.*,
                   (SELECT COUNT(*) FROM group_games gg WHERE gg.group_id = g.id) as game_count,
                   (SELECT COUNT(*) FROM group_materials gm WHERE gm.group_id = g.id) as material_count
            FROM groups g
            WHERE g.name LIKE :query1 OR g.description LIKE :query2
            ORDER BY g.name ASC
            LIMIT 50
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute(['query1' => $searchTerm, 'query2' => $searchTerm]);
        return $stmt->fetchAll();
    }
}
