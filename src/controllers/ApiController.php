<?php
/**
 * =====================================================================================
 * API CONTROLLER - AJAX/JSON Endpoint Handler
 * =====================================================================================
 *
 * PURPOSE:
 * Handles all AJAX/API requests from the frontend JavaScript (app.js).
 * All responses are JSON. Provides endpoints for image upload/delete,
 * duplicate checking, autocomplete search, quick-create, favorite toggling,
 * random game selection, and group item management.
 *
 * AUTHENTICATION:
 * - All endpoints require authentication EXCEPT those listed in isPublicEndpoint()
 * - Auth check happens in __construct() before any action runs
 * - CSRF is required on all state-changing (POST) endpoints
 *
 * RATE LIMITING:
 * - Applied per-endpoint via rateLimit() method
 * - Uses IP-based file locking (see helpers/security.php checkRateLimit())
 *
 * RESPONSE FORMAT:
 * - Success: {"success": true, "data": ...}
 * - Error: {"success": false, "error": "message"}
 *
 * ENDPOINTS (defined in src/config/routes.php):
 * - POST /api/upload-image - Upload and process image
 * - POST /api/delete-image - Delete uploaded image
 * - GET  /api/check-duplicate - Check for duplicate names
 * - GET  /api/search/tags - Autocomplete search for tags
 * - GET  /api/search/materials - Autocomplete search for materials
 * - GET  /api/search/games - Autocomplete search for games
 * - GET  /api/live-search - Global live search across all entities
 * - POST /api/quick-create/tag - Create tag inline (from game form)
 * - POST /api/quick-create/material - Create material inline (from game form)
 * - GET  /api/tags - Get all tags for dropdowns
 * - GET  /api/materials - Get all materials for dropdowns
 * - GET  /api/categories - Get all categories for dropdowns
 * - GET  /api/boxes - Get all boxes for dropdowns
 * - GET  /api/groups - Get all groups for dropdowns
 * - POST /api/toggle-favorite - Toggle favorite status on game/material
 * - GET  /api/random-game - Get a random game with optional filters
 * - POST /api/group/add-item - Add game/material to a group
 * - POST /api/group/remove-item - Remove game/material from a group
 * - GET  /api/health - Public health check endpoint (no auth required)
 *
 * AI NOTES:
 * - This controller overrides the parent json() method with its own private version
 *   that includes JSON_UNESCAPED_UNICODE for German character support
 * - Image upload returns the relative path for storage in the database
 * - Autocomplete endpoints return arrays of {id, name, ...} for Select2/similar widgets
 * - Quick-create endpoints are called from inline "add new" buttons in game forms
 *
 * RELATED FILES:
 * - public/assets/js/app.js - Frontend JavaScript that calls these endpoints
 * - src/services/ImageProcessor.php - Image upload processing
 * - src/config/routes.php - Route definitions mapping URLs to methods
 *
 * @package KindergartenOrganizer\Controllers
 * @since 1.0.0
 * =====================================================================================
 */

class ApiController extends Controller
{
    public function __construct()
    {
        // AI NOTE: Auth check runs before any action. Public endpoints skip this.
        if (!$this->isPublicEndpoint()) {
            $this->requireAuth();
        }
    }

    /**
     * Check if the current request targets a public (no-auth) endpoint.
     *
     * AI NOTE: Uses parse_url() to extract only the path component, then compares
     * with exact match. Previously used str_contains() which was vulnerable to
     * auth bypass (e.g., /api/upload?x=/api/health would match).
     *
     * SECURITY: Must use exact path matching, not substring matching.
     */
    private function isPublicEndpoint(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH);
        $path = '/' . trim($path, '/');

        $publicEndpoints = [
            '/api/health',
        ];

        return in_array($path, $publicEndpoints, true);
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
                'error' => __('api.rate_limit'),
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
            $this->jsonError(__('validation.invalid_image_type'), 400);
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
            $this->jsonError(__('validation.no_file_uploaded'), 400);
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
            $this->jsonError(__('validation.no_path'), 400);
            return;
        }

        // Security: Validate path matches expected pattern to prevent path traversal
        // Expected format: type/full/filename.webp (e.g., games/full/20240101_abc123.webp)
        $allowedTypes = ['games', 'boxes', 'categories', 'tags', 'materials'];
        $allowedSubdirs = ['full', 'thumbs'];

        if (!preg_match('#^([a-z]+)/(full|thumbs)/([a-zA-Z0-9_]+\.webp)$#', $path, $matches)) {
            $this->jsonError(__('validation.invalid_image_path'), 400);
            return;
        }

        $type = $matches[1];
        $subdir = $matches[2];
        $filename = $matches[3];

        // Validate type against whitelist
        if (!in_array($type, $allowedTypes, true)) {
            $this->jsonError(__('validation.invalid_image_type'), 400);
            return;
        }

        // Reconstruct the safe path
        $safePath = $type . '/' . $subdir . '/' . $filename;

        require_once SRC_PATH . '/services/ImageProcessor.php';
        $processor = new ImageProcessor();

        if ($processor->delete($safePath)) {
            $this->json(['success' => true]);
        } else {
            $this->jsonError(__('flash.error_deleting_image'), 500);
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
            $this->jsonError(__('validation.type_value_required'), 400);
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

            case 'locations':
                require_once SRC_PATH . '/models/Location.php';
                $exists = Location::nameExists($value, $excludeId);
                break;

            default:
                $this->jsonError(__('validation.invalid_type'), 400);
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
        $games = Game::searchGames($query, 10);

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

        // Limit query length to prevent abuse
        if (strlen($query) > 100) {
            $query = substr($query, 0, 100);
        }

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

        // Equal limits for all types (global search)
        $gameLim = 6;
        $matLim = 6;
        $boxLim = 4;
        $tagLim = 4;
        $grpLim = 4;

        // Search games
        $games = Game::searchGames($query, $gameLim);
        foreach ($games as $game) {
            $item = [
                'type' => 'game',
                'id' => $game['id'],
                'name' => $game['name'],
                'url' => url('/games/' . $game['id']),
                'image' => $game['image_path'] ? upload($game['image_path']) : null,
            ];
            $results[] = $item;
        }

        // Search materials
        $stmt = $db->prepare("SELECT id, name, image_path FROM materials WHERE name LIKE :q ORDER BY name LIMIT " . (int)$matLim);
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

        // Search boxes
        $stmt = $db->prepare("SELECT id, name FROM boxes WHERE name LIKE :q1 OR label LIKE :q2 ORDER BY name LIMIT " . (int)$boxLim);
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

        // Search tags
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

        // Search groups
        $stmt = $db->prepare("SELECT id, name FROM groups WHERE name LIKE :q ORDER BY name LIMIT " . (int)$grpLim);
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
            $this->jsonError(__('validation.name_required'), 400);
            return;
        }

        if (strlen($name) > 100) {
            $this->jsonError(__('validation.name_max_100'), 400);
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
            $this->jsonError(__('flash.error_creating'), 500);
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
            $this->jsonError(__('validation.name_required'), 400);
            return;
        }

        if (strlen($name) > 100) {
            $this->jsonError(__('validation.name_max_100'), 400);
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
            $this->jsonError(__('flash.error_creating'), 500);
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
            $this->jsonError(__('game.not_found'), 404);
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
            $this->jsonError(__('material.not_found'), 404);
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
                'message' => __('game.not_found'),
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

        $this->json(['groups' => $groups]);
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
            $this->jsonError(__('api.group_type_item_required'), 400);
            return;
        }

        if (!in_array($itemType, ['game', 'material'], true)) {
            $this->jsonError(__('api.invalid_item_type'), 400);
            return;
        }

        $result = Group::addItem($groupId, $itemType, $itemId);

        if ($result) {
            $this->json(['success' => true]);
        } else {
            $this->jsonError(__('api.add_item_failed'), 500);
        }
    }

    /**
     * Remove item from group
     *
     * AI NOTE: Validates item_type against whitelist to match addItemToGroup().
     * Without this check, arbitrary strings could be passed to Group::removeItem().
     */
    public function removeItemFromGroup(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Group.php';

        $groupId = (int)$this->getPost('group_id', 0);
        $itemType = $this->getPost('item_type', '');
        $itemId = (int)$this->getPost('item_id', 0);

        if (!$groupId || !$itemType || !$itemId) {
            $this->jsonError(__('api.group_type_item_required'), 400);
            return;
        }

        if (!in_array($itemType, ['game', 'material'], true)) {
            $this->jsonError(__('api.invalid_item_type'), 400);
            return;
        }

        $result = Group::removeItem($groupId, $itemType, $itemId);

        if ($result) {
            $this->json(['success' => true]);
        } else {
            $this->jsonError(__('api.remove_item_failed'), 500);
        }
    }

    /**
     * Send JSON response
     */
    protected function json(array $data, int $status = 200): void
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
}
