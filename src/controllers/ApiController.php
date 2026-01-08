<?php
/**
 * API Controller
 *
 * Handles AJAX endpoints for image upload, duplicate checking, search, etc.
 */

class ApiController extends Controller
{
    public function __construct()
    {
        // Most API endpoints require authentication
        if (!$this->isPublicEndpoint()) {
            $this->requireAuth();
        }
    }

    /**
     * Check if the current endpoint is public
     */
    private function isPublicEndpoint(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $publicEndpoints = [
            '/api/health',
        ];

        foreach ($publicEndpoints as $endpoint) {
            if (str_contains($uri, $endpoint)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply rate limiting to an endpoint
     */
    private function rateLimit(string $endpoint, int $maxAttempts = 60, int $decaySeconds = 60): void
    {
        $ip = getClientIp();
        $key = "api:{$endpoint}:{$ip}";

        if (!checkRateLimit($key, $maxAttempts, $decaySeconds)) {
            $this->json([
                'success' => false,
                'error' => 'Zu viele Anfragen. Bitte warten Sie einen Moment.',
            ], 429);
        }
    }

    /**
     * Health check endpoint
     */
    public function health(): void
    {
        $this->json([
            'status' => 'ok',
            'timestamp' => date('c'),
        ]);
    }

    /**
     * Upload image
     */
    public function uploadImage(): void
    {
        $this->rateLimit('upload', 30, 60); // 30 uploads per minute
        $this->requireCsrf();

        $type = $this->getPost('type', '');
        $allowedTypes = ['games', 'boxes', 'categories', 'tags', 'materials'];

        if (!in_array($type, $allowedTypes)) {
            $this->jsonError('Ungültiger Bildtyp.', 400);
            return;
        }

        // Check if it's a base64 upload (from Cropper.js)
        $base64Data = $this->getPost('image_data', '');
        if ($base64Data) {
            require_once SRC_PATH . '/services/ImageProcessor.php';
            $processor = new ImageProcessor();
            $result = $processor->processBase64($base64Data, $type);

            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'path' => $result['path'],
                    'url' => upload($result['path']),
                ]);
            } else {
                $this->jsonError($result['error'], 400);
            }
            return;
        }

        // Regular file upload
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->jsonError('Keine Datei hochgeladen.', 400);
            return;
        }

        // Get crop data if provided
        $cropData = null;
        $cropJson = $this->getPost('crop_data', '');
        if ($cropJson) {
            $cropData = json_decode($cropJson, true);
        }

        require_once SRC_PATH . '/services/ImageProcessor.php';
        $processor = new ImageProcessor();
        $result = $processor->process($_FILES['image'], $type, $cropData);

        if ($result['success']) {
            $this->json([
                'success' => true,
                'path' => $result['path'],
                'url' => upload($result['path']),
            ]);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }

    /**
     * Delete image
     */
    public function deleteImage(): void
    {
        $this->requireCsrf();

        $path = $this->getPost('path', '');

        if (empty($path)) {
            $this->jsonError('Kein Bildpfad angegeben.', 400);
            return;
        }

        // Sanitize path to prevent directory traversal
        $path = basename(dirname(dirname($path))) . '/' . basename(dirname($path)) . '/' . basename($path);

        require_once SRC_PATH . '/services/ImageProcessor.php';
        $processor = new ImageProcessor();

        if ($processor->delete($path)) {
            $this->json(['success' => true]);
        } else {
            $this->jsonError('Fehler beim Löschen des Bildes.', 500);
        }
    }

    /**
     * Check for duplicate values
     */
    public function checkDuplicate(): void
    {
        $type = $this->getQuery('type', '');
        $value = $this->getQuery('value', '');
        $excludeId = $this->getQuery('exclude_id', '');

        if (empty($type) || empty($value)) {
            $this->jsonError('Typ und Wert sind erforderlich.', 400);
            return;
        }

        $excludeId = $excludeId ? (int)$excludeId : null;
        $exists = false;

        switch ($type) {
            case 'boxes':
                require_once SRC_PATH . '/models/Box.php';
                $exists = Box::nameExists($value, $excludeId);
                break;

            case 'categories':
                require_once SRC_PATH . '/models/Category.php';
                $exists = Category::nameExists($value, $excludeId);
                break;

            case 'tags':
                require_once SRC_PATH . '/models/Tag.php';
                $exists = Tag::nameExists($value, $excludeId);
                break;

            case 'materials':
                require_once SRC_PATH . '/models/Material.php';
                $exists = Material::nameExists($value, $excludeId);
                break;

            case 'games':
                require_once SRC_PATH . '/models/Game.php';
                $exists = Game::nameExists($value, $excludeId);
                break;

            case 'groups':
                require_once SRC_PATH . '/models/Group.php';
                $exists = Group::nameExists($value, $excludeId);
                break;

            default:
                $this->jsonError('Ungültiger Typ.', 400);
                return;
        }

        $this->json([
            'exists' => $exists,
            'message' => $exists ? __('validation.duplicate') : null,
        ]);
    }

    /**
     * Search tags (for autocomplete)
     */
    public function searchTags(): void
    {
        $query = $this->getQuery('q', '');

        if (strlen($query) < 1) {
            $this->json(['results' => []]);
            return;
        }

        require_once SRC_PATH . '/models/Tag.php';
        $tags = Tag::searchByName($query, 10);

        $results = array_map(function($tag) {
            return [
                'id' => $tag['id'],
                'name' => $tag['name'],
                'color' => $tag['color'],
            ];
        }, $tags);

        $this->json(['results' => $results]);
    }

    /**
     * Search materials (for autocomplete)
     */
    public function searchMaterials(): void
    {
        $query = $this->getQuery('q', '');

        if (strlen($query) < 1) {
            $this->json(['results' => []]);
            return;
        }

        require_once SRC_PATH . '/models/Material.php';
        $materials = Material::searchByName($query, 10);

        $results = array_map(function($material) {
            return [
                'id' => $material['id'],
                'name' => $material['name'],
            ];
        }, $materials);

        $this->json(['results' => $results]);
    }

    /**
     * Search games (for autocomplete)
     */
    public function searchGames(): void
    {
        $query = $this->getQuery('q', '');

        if (strlen($query) < 2) {
            $this->json(['results' => []]);
            return;
        }

        require_once SRC_PATH . '/models/Game.php';
        $games = Game::search($query, 10);

        $results = array_map(function($game) {
            return [
                'id' => $game['id'],
                'name' => $game['name'],
                'image_path' => $game['image_path'] ? upload($game['image_path']) : null,
            ];
        }, $games);

        $this->json(['results' => $results]);
    }

    /**
     * Live search (combined results for header dropdown)
     */
    public function liveSearch(): void
    {
        $this->rateLimit('search', 120, 60); // 120 searches per minute
        $query = trim($this->getQuery('q', ''));

        if (strlen($query) < 2) {
            $this->json(['results' => []]);
            return;
        }

        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/models/Material.php';
        require_once SRC_PATH . '/models/Box.php';
        require_once SRC_PATH . '/models/Tag.php';
        require_once SRC_PATH . '/models/Group.php';

        $results = [];
        $db = Database::getInstance();
        $searchTerm = '%' . $query . '%';

        // Search games (limit 5)
        $games = Game::search($query, 5);
        foreach ($games as $game) {
            $results[] = [
                'type' => 'game',
                'id' => $game['id'],
                'name' => $game['name'],
                'url' => url('/games/' . $game['id']),
                'image' => $game['image_path'] ? upload($game['image_path']) : null,
            ];
        }

        // Search materials (limit 3)
        $stmt = $db->prepare("SELECT id, name, image_path FROM materials WHERE name LIKE :q ORDER BY name LIMIT 3");
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

        // Search boxes (limit 2)
        $stmt = $db->prepare("SELECT id, name FROM boxes WHERE name LIKE :q OR label LIKE :q ORDER BY name LIMIT 2");
        $stmt->execute(['q' => $searchTerm]);
        foreach ($stmt->fetchAll() as $box) {
            $results[] = [
                'type' => 'box',
                'id' => $box['id'],
                'name' => $box['name'],
                'url' => url('/boxes/' . $box['id']),
                'image' => null,
            ];
        }

        // Search tags (limit 2)
        $tags = Tag::searchByName($query, 2);
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

        // Search groups (limit 2)
        $stmt = $db->prepare("SELECT id, name FROM groups WHERE name LIKE :q ORDER BY name LIMIT 2");
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

        $this->json([
            'results' => $results,
            'query' => $query,
            'more_url' => url('/search', ['q' => $query]),
        ]);
    }

    /**
     * Quick create a tag
     */
    public function quickCreateTag(): void
    {
        $this->requireCsrf();

        $name = trim($this->getPost('name', ''));

        if (empty($name)) {
            $this->jsonError('Name ist erforderlich.', 400);
            return;
        }

        if (strlen($name) > 100) {
            $this->jsonError('Name darf maximal 100 Zeichen haben.', 400);
            return;
        }

        require_once SRC_PATH . '/models/Tag.php';

        // Check if already exists
        if (Tag::nameExists($name)) {
            $this->jsonError(__('validation.duplicate'), 400);
            return;
        }

        $tagId = Tag::quickCreate($name);

        if ($tagId) {
            // Log creation
            require_once SRC_PATH . '/services/ChangelogService.php';
            ChangelogService::getInstance()->logCreate('tag', $tagId, $name, ['name' => $name]);

            $this->json([
                'success' => true,
                'tag' => [
                    'id' => $tagId,
                    'name' => $name,
                    'color' => null,
                ],
            ]);
        } else {
            $this->jsonError('Fehler beim Erstellen des Themas.', 500);
        }
    }

    /**
     * Quick create a material
     */
    public function quickCreateMaterial(): void
    {
        $this->requireCsrf();

        $name = trim($this->getPost('name', ''));

        if (empty($name)) {
            $this->jsonError('Name ist erforderlich.', 400);
            return;
        }

        if (strlen($name) > 100) {
            $this->jsonError('Name darf maximal 100 Zeichen haben.', 400);
            return;
        }

        require_once SRC_PATH . '/models/Material.php';

        // Check if already exists
        if (Material::nameExists($name)) {
            $this->jsonError(__('validation.duplicate'), 400);
            return;
        }

        $materialId = Material::quickCreate($name);

        if ($materialId) {
            // Log creation
            require_once SRC_PATH . '/services/ChangelogService.php';
            ChangelogService::getInstance()->logCreate('material', $materialId, $name, ['name' => $name]);

            $this->json([
                'success' => true,
                'material' => [
                    'id' => $materialId,
                    'name' => $name,
                ],
            ]);
        } else {
            $this->jsonError('Fehler beim Erstellen des Materials.', 500);
        }
    }

    /**
     * Get boxes for select dropdown
     */
    public function getBoxes(): void
    {
        require_once SRC_PATH . '/models/Box.php';
        $boxes = Box::getForSelect();

        $this->json(['boxes' => $boxes]);
    }

    /**
     * Get categories for select dropdown
     */
    public function getCategories(): void
    {
        require_once SRC_PATH . '/models/Category.php';
        $categories = Category::getForSelect();

        $this->json(['categories' => $categories]);
    }

    /**
     * Get tags for select dropdown
     */
    public function getTags(): void
    {
        require_once SRC_PATH . '/models/Tag.php';
        $tags = Tag::getForSelect();

        $this->json(['tags' => $tags]);
    }

    /**
     * Get materials for select dropdown
     */
    public function getMaterials(): void
    {
        require_once SRC_PATH . '/models/Material.php';
        $materials = Material::getForSelect();

        $this->json(['materials' => $materials]);
    }

    /**
     * Get games for a box
     */
    public function getBoxGames(string $boxId): void
    {
        require_once SRC_PATH . '/models/Box.php';
        $games = Box::getGames((int)$boxId);

        $results = array_map(function($game) {
            return [
                'id' => $game['id'],
                'name' => $game['name'],
                'image_path' => $game['image_path'] ? upload($game['image_path']) : null,
            ];
        }, $games);

        $this->json(['games' => $results]);
    }

    /**
     * Get games for a category
     */
    public function getCategoryGames(string $categoryId): void
    {
        require_once SRC_PATH . '/models/Category.php';
        $games = Category::getGames((int)$categoryId);

        $results = array_map(function($game) {
            return [
                'id' => $game['id'],
                'name' => $game['name'],
                'image_path' => $game['image_path'] ? upload($game['image_path']) : null,
            ];
        }, $games);

        $this->json(['games' => $results]);
    }

    /**
     * Get games for a tag
     */
    public function getTagGames(string $tagId): void
    {
        require_once SRC_PATH . '/models/Tag.php';
        $games = Tag::getGames((int)$tagId);

        $results = array_map(function($game) {
            return [
                'id' => $game['id'],
                'name' => $game['name'],
                'image_path' => $game['image_path'] ? upload($game['image_path']) : null,
            ];
        }, $games);

        $this->json(['games' => $results]);
    }

    /**
     * Toggle game favorite status
     */
    public function toggleGameFavorite(string $gameId): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Game.php';

        $game = Game::find((int)$gameId);
        if (!$game) {
            $this->jsonError('Spiel nicht gefunden.', 404);
            return;
        }

        $isFavorite = Game::toggleFavorite((int)$gameId);

        $this->json([
            'success' => true,
            'is_favorite' => $isFavorite,
        ]);
    }

    /**
     * Toggle material favorite status
     */
    public function toggleMaterialFavorite(string $materialId): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Material.php';

        $material = Material::find((int)$materialId);
        if (!$material) {
            $this->jsonError('Material nicht gefunden.', 404);
            return;
        }

        $isFavorite = Material::toggleFavorite((int)$materialId);

        $this->json([
            'success' => true,
            'is_favorite' => $isFavorite,
        ]);
    }

    /**
     * Get a random game with optional filters
     */
    public function getRandomGame(): void
    {
        require_once SRC_PATH . '/models/Game.php';

        $filters = [];

        if (!empty($_GET['category_id'])) {
            $filters['category_id'] = (int)$_GET['category_id'];
        }

        if (!empty($_GET['tag_id'])) {
            $filters['tag_id'] = (int)$_GET['tag_id'];
        }

        if (isset($_GET['is_outdoor'])) {
            $filters['is_outdoor'] = (int)$_GET['is_outdoor'];
        }

        if (!empty($_GET['max_players'])) {
            $filters['max_players'] = (int)$_GET['max_players'];
        }

        $game = Game::random($filters);

        if ($game) {
            $this->json([
                'success' => true,
                'game' => [
                    'id' => $game['id'],
                    'name' => $game['name'],
                    'description' => $game['description'],
                    'image_path' => $game['image_path'] ? upload($game['image_path']) : null,
                    'box_name' => $game['box_name'] ?? null,
                    'box_label' => $game['box_label'] ?? null,
                ],
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Kein passendes Spiel gefunden.',
            ]);
        }
    }

    /**
     * Get all groups for select dropdown
     */
    public function getGroups(): void
    {
        require_once SRC_PATH . '/models/Group.php';

        $groups = Group::getForSelect();

        $this->json($groups);
    }

    /**
     * Add item to group
     */
    public function addItemToGroup(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Group.php';

        $groupId = (int)$this->getPost('group_id', 0);
        $itemType = $this->getPost('item_type', '');
        $itemId = (int)$this->getPost('item_id', 0);

        if (!$groupId || !$itemType || !$itemId) {
            $this->jsonError('Gruppe, Typ und Element sind erforderlich.', 400);
            return;
        }

        if (!in_array($itemType, ['game', 'material'])) {
            $this->jsonError('Ungültiger Elementtyp.', 400);
            return;
        }

        $result = Group::addItem($groupId, $itemType, $itemId);

        if ($result) {
            $this->json(['success' => true]);
        } else {
            $this->jsonError('Element konnte nicht hinzugefügt werden.', 500);
        }
    }

    /**
     * Remove item from group
     */
    public function removeItemFromGroup(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Group.php';

        $groupId = (int)$this->getPost('group_id', 0);
        $itemType = $this->getPost('item_type', '');
        $itemId = (int)$this->getPost('item_id', 0);

        if (!$groupId || !$itemType || !$itemId) {
            $this->jsonError('Gruppe, Typ und Element sind erforderlich.', 400);
            return;
        }

        $result = Group::removeItem($groupId, $itemType, $itemId);

        if ($result) {
            $this->json(['success' => true]);
        } else {
            $this->jsonError('Element konnte nicht entfernt werden.', 500);
        }
    }

    /**
     * Send JSON response
     */
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send JSON error response
     */
    private function jsonError(string $message, int $status = 400): void
    {
        $this->json([
            'success' => false,
            'error' => $message,
        ], $status);
    }

    /**
     * Get POST parameter
     */
    private function getPost(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET parameter
     */
    private function getQuery(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}
