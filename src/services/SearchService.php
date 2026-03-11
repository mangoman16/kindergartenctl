<?php
declare(strict_types=1);

class SearchService
{
    /**
     * Full-page search across all entity types.
     */
    public function search(string $query, string $type = 'all'): ServiceResult
    {
        if (empty($query)) {
            return ServiceResult::ok(['results' => [], 'counts' => []]);
        }

        $results = $this->performSearch($query, $type);
        $counts = $this->getCounts($query);

        return ServiceResult::ok([
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'counts' => $counts,
        ]);
    }

    /**
     * Live search for the command palette / header dropdown.
     */
    public function liveSearch(string $query, int $limit = 24): ServiceResult
    {
        if (mb_strlen($query) > 100) {
            $query = mb_substr($query, 0, 100);
        }
        if (mb_strlen($query) < 2) {
            return ServiceResult::ok(['results' => [], 'query' => $query]);
        }

        $db = Database::getInstance();
        $likeQuery = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
        $searchTerm = '%' . $likeQuery . '%';
        $results = [];

        $gameLim = 6;
        $matLim = 6;
        $boxLim = 4;
        $tagLim = 4;
        $grpLim = 4;

        // Games
        $games = Game::searchGames($query, $gameLim);
        foreach ($games as $game) {
            $results[] = [
                'type' => 'game',
                'id' => $game['id'],
                'name' => $game['name'],
                'url' => url('/games/' . $game['id']),
                'image' => $game['image_path'] ? upload($game['image_path']) : null,
            ];
        }

        // Materials
        $stmt = $db->prepare("SELECT id, name, image_path FROM materials WHERE name LIKE :q ORDER BY name LIMIT " . $matLim);
        $stmt->execute(['q' => $searchTerm]);
        foreach ($stmt->fetchAll() as $material) {
            $results[] = [
                'type' => 'material',
                'id' => $material['id'],
                'name' => $material['name'],
                'url' => url('/materials/' . $material['id']),
                'image' => $material['image_path'] ? upload($material['image_path']) : null,
            ];
        }

        // Boxes
        $stmt = $db->prepare("SELECT id, name FROM boxes WHERE name LIKE :q1 OR label LIKE :q2 ORDER BY name LIMIT " . $boxLim);
        $stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm]);
        foreach ($stmt->fetchAll() as $box) {
            $results[] = [
                'type' => 'box',
                'id' => $box['id'],
                'name' => $box['name'],
                'url' => url('/boxes/' . $box['id']),
                'image' => null,
            ];
        }

        // Tags
        $tags = Tag::searchByName($query, $tagLim);
        foreach ($tags as $tag) {
            $results[] = [
                'type' => 'tag',
                'id' => $tag['id'],
                'name' => $tag['name'],
                'url' => url('/games?tag=' . $tag['id']),
                'image' => null,
                'color' => $tag['color'] ?? null,
            ];
        }

        // Groups
        $stmt = $db->prepare("SELECT id, name FROM groups WHERE name LIKE :q ORDER BY name LIMIT " . $grpLim);
        $stmt->execute(['q' => $searchTerm]);
        foreach ($stmt->fetchAll() as $group) {
            $results[] = [
                'type' => 'group',
                'id' => $group['id'],
                'name' => $group['name'],
                'url' => url('/groups/' . $group['id']),
                'image' => null,
            ];
        }

        return ServiceResult::ok([
            'results' => $results,
            'query' => $query,
            'more_url' => url('/search', ['q' => $query]),
        ]);
    }

    // ------------------------------------------------------------------
    // Internal helpers (extracted from SearchController)
    // ------------------------------------------------------------------

    private function performSearch(string $query, string $type): array
    {
        $results = [];

        if ($type === 'all' || $type === 'games') {
            $results['games'] = Game::allWithRelations(['search' => $query], 'name', 'ASC');
        }
        if ($type === 'all' || $type === 'materials') {
            $results['materials'] = $this->searchMaterials($query);
        }
        if ($type === 'all' || $type === 'boxes') {
            $results['boxes'] = $this->searchBoxes($query);
        }
        if ($type === 'all' || $type === 'tags') {
            $results['tags'] = Tag::searchByName($query, 50);
        }
        if ($type === 'all' || $type === 'groups') {
            $results['groups'] = $this->searchGroups($query);
        }

        return $results;
    }

    private function getCounts(string $query): array
    {
        return [
            'games' => count(Game::allWithRelations(['search' => $query], 'name', 'ASC')),
            'materials' => count($this->searchMaterials($query)),
            'boxes' => count($this->searchBoxes($query)),
            'tags' => count(Tag::searchByName($query, 100)),
            'groups' => count($this->searchGroups($query)),
        ];
    }

    private function searchMaterials(string $query): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM materials
            WHERE name LIKE :query1 OR description LIKE :query2
            ORDER BY name ASC LIMIT 50
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute(['query1' => $searchTerm, 'query2' => $searchTerm]);
        return $stmt->fetchAll();
    }

    private function searchBoxes(string $query): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT b.*, COUNT(m.id) as material_count, l.name as location_name
            FROM boxes b
            LEFT JOIN materials m ON m.box_id = b.id
            LEFT JOIN locations l ON l.id = b.location_id
            WHERE b.name LIKE :query1 OR b.label LIKE :query2 OR l.name LIKE :query3
            GROUP BY b.id ORDER BY b.name ASC LIMIT 50
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute(['query1' => $searchTerm, 'query2' => $searchTerm, 'query3' => $searchTerm]);
        return $stmt->fetchAll();
    }

    private function searchGroups(string $query): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT g.*,
                   (SELECT COUNT(*) FROM group_games gg WHERE gg.group_id = g.id) as game_count,
                   (SELECT COUNT(*) FROM group_materials gm WHERE gm.group_id = g.id) as material_count
            FROM groups g
            WHERE g.name LIKE :query1 OR g.description LIKE :query2
            ORDER BY g.name ASC LIMIT 50
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute(['query1' => $searchTerm, 'query2' => $searchTerm]);
        return $stmt->fetchAll();
    }
}
