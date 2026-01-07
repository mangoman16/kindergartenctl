<?php
/**
 * Game Controller
 */

class GameController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * List all games
     */
    public function index(): void
    {
        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/models/Box.php';
        require_once SRC_PATH . '/models/Category.php';
        require_once SRC_PATH . '/models/Tag.php';

        $filters = [
            'box_id' => $this->getQuery('box') ?: null,
            'category_id' => $this->getQuery('category') ?: null,
            'tag_id' => $this->getQuery('tag') ?: null,
            'is_outdoor' => $this->getQuery('outdoor') !== null ? (int)$this->getQuery('outdoor') : null,
            'is_active' => $this->getQuery('active') !== null ? (int)$this->getQuery('active') : null,
            'is_favorite' => $this->getQuery('favorites') !== null ? (int)$this->getQuery('favorites') : null,
            'search' => $this->getQuery('q') ?: null,
        ];

        $sort = $this->getQuery('sort', 'name');
        $order = $this->getQuery('order', 'asc');

        $games = Game::allWithRelations($filters, $sort, $order);

        // Get filter options
        $boxes = Box::getForSelect();
        $categories = Category::getForSelect();
        $tags = Tag::getForSelect();

        $this->setTitle(__('game.title_plural'));
        $this->addBreadcrumb(__('game.title_plural'), url('/games'));

        $this->render('games/index', [
            'games' => $games,
            'boxes' => $boxes,
            'categories' => $categories,
            'tags' => $tags,
            'filters' => $filters,
            'currentSort' => $sort,
            'currentOrder' => $order,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        require_once SRC_PATH . '/models/Box.php';
        require_once SRC_PATH . '/models/Category.php';
        require_once SRC_PATH . '/models/Tag.php';
        require_once SRC_PATH . '/models/Material.php';

        $this->setTitle(__('game.create'));
        $this->addBreadcrumb(__('game.title_plural'), url('/games'));
        $this->addBreadcrumb(__('game.create'));

        $this->render('games/form', [
            'game' => null,
            'isEdit' => false,
            'boxes' => Box::getForSelect(),
            'categories' => Category::getForSelect(),
            'tags' => Tag::getForSelect(),
            'materials' => Material::getForSelect(),
            'selectedTags' => [],
            'selectedMaterials' => [],
        ]);
    }

    /**
     * Store new game
     */
    public function store(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'instructions' => trim($this->getPost('instructions', '')),
            'min_players' => $this->getPost('min_players') ? (int)$this->getPost('min_players') : null,
            'max_players' => $this->getPost('max_players') ? (int)$this->getPost('max_players') : null,
            'duration_minutes' => $this->getPost('duration_minutes') ? (int)$this->getPost('duration_minutes') : null,
            'is_outdoor' => $this->getPost('is_outdoor') ? 1 : 0,
            'is_active' => $this->getPost('is_active') ? 1 : 0,
            'image_path' => $this->getPost('image_path', ''),
            'box_id' => $this->getPost('box_id') ? (int)$this->getPost('box_id') : null,
            'category_id' => $this->getPost('category_id') ? (int)$this->getPost('category_id') : null,
        ];

        $tagIds = $this->getPost('tags', []);
        $materials = $this->parseMaterials($this->getPost('materials', []));

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:255',
        ]);

        // Check duplicate name
        if (!empty($data['name']) && Game::nameExists($data['name'])) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput(array_merge($data, ['tags' => $tagIds, 'materials' => $materials]));
            $this->redirect('/games/create');
            return;
        }

        // Create game
        $gameId = Game::create($data);

        if (!$gameId) {
            Session::setFlash('error', 'Fehler beim Erstellen des Spiels.');
            Session::setOldInput(array_merge($data, ['tags' => $tagIds, 'materials' => $materials]));
            $this->redirect('/games/create');
            return;
        }

        // Update tags and materials
        Game::updateTags($gameId, $tagIds);
        Game::updateMaterials($gameId, $materials);

        // Log change
        ChangelogService::getInstance()->logCreate('game', $gameId, $data['name'], $data);

        Session::setFlash('success', __('flash.created', ['item' => __('game.title')]));
        $this->redirect('/games/' . $gameId);
    }

    /**
     * Show game details
     */
    public function show(string $id): void
    {
        require_once SRC_PATH . '/models/Game.php';

        $game = Game::findWithRelations((int)$id);

        if (!$game) {
            Session::setFlash('error', 'Spiel nicht gefunden.');
            $this->redirect('/games');
            return;
        }

        $this->setTitle($game['name']);
        $this->addBreadcrumb(__('game.title_plural'), url('/games'));
        $this->addBreadcrumb($game['name']);

        $this->render('games/show', [
            'game' => $game,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/models/Box.php';
        require_once SRC_PATH . '/models/Category.php';
        require_once SRC_PATH . '/models/Tag.php';
        require_once SRC_PATH . '/models/Material.php';

        $game = Game::findWithRelations((int)$id);

        if (!$game) {
            Session::setFlash('error', 'Spiel nicht gefunden.');
            $this->redirect('/games');
            return;
        }

        $this->setTitle(__('game.edit'));
        $this->addBreadcrumb(__('game.title_plural'), url('/games'));
        $this->addBreadcrumb($game['name'], url('/games/' . $id));
        $this->addBreadcrumb(__('action.edit'));

        $this->render('games/form', [
            'game' => $game,
            'isEdit' => true,
            'boxes' => Box::getForSelect(),
            'categories' => Category::getForSelect(),
            'tags' => Tag::getForSelect(),
            'materials' => Material::getForSelect(),
            'selectedTags' => array_column($game['tags'], 'id'),
            'selectedMaterials' => $game['materials'],
        ]);
    }

    /**
     * Update game
     */
    public function update(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $game = Game::find((int)$id);

        if (!$game) {
            Session::setFlash('error', 'Spiel nicht gefunden.');
            $this->redirect('/games');
            return;
        }

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'instructions' => trim($this->getPost('instructions', '')),
            'min_players' => $this->getPost('min_players') ? (int)$this->getPost('min_players') : null,
            'max_players' => $this->getPost('max_players') ? (int)$this->getPost('max_players') : null,
            'duration_minutes' => $this->getPost('duration_minutes') ? (int)$this->getPost('duration_minutes') : null,
            'is_outdoor' => $this->getPost('is_outdoor') ? 1 : 0,
            'is_active' => $this->getPost('is_active') ? 1 : 0,
            'image_path' => $this->getPost('image_path', '') ?: $game['image_path'],
            'box_id' => $this->getPost('box_id') ? (int)$this->getPost('box_id') : null,
            'category_id' => $this->getPost('category_id') ? (int)$this->getPost('category_id') : null,
        ];

        $tagIds = $this->getPost('tags', []);
        $materials = $this->parseMaterials($this->getPost('materials', []));

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:255',
        ]);

        // Check duplicate name (excluding current)
        if (!empty($data['name']) && Game::nameExists($data['name'], (int)$id)) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput(array_merge($data, ['tags' => $tagIds, 'materials' => $materials]));
            $this->redirect('/games/' . $id . '/edit');
            return;
        }

        // Track changes
        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($game, $data, [
            'name', 'description', 'instructions', 'min_players', 'max_players',
            'duration_minutes', 'is_outdoor', 'is_active', 'image_path', 'box_id', 'category_id'
        ]);

        // Update game
        Game::update((int)$id, $data);

        // Update tags and materials
        Game::updateTags((int)$id, $tagIds);
        Game::updateMaterials((int)$id, $materials);

        // Log change
        if (!empty($changes)) {
            $changelog->logUpdate('game', (int)$id, $data['name'], $changes);
        }

        Session::setFlash('success', __('flash.updated', ['item' => __('game.title')]));
        $this->redirect('/games/' . $id);
    }

    /**
     * Delete game
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/services/ChangelogService.php';
        require_once SRC_PATH . '/services/ImageProcessor.php';

        $game = Game::find((int)$id);

        if (!$game) {
            Session::setFlash('error', 'Spiel nicht gefunden.');
            $this->redirect('/games');
            return;
        }

        // Log change before deletion
        ChangelogService::getInstance()->logDelete('game', (int)$id, $game['name'], $game);

        // Delete game (tags and materials entries will be deleted due to foreign key)
        Game::delete((int)$id);

        // Delete image if exists
        if ($game['image_path']) {
            $processor = new ImageProcessor();
            $processor->delete($game['image_path']);
        }

        Session::setFlash('success', __('flash.deleted', ['item' => __('game.title')]));
        $this->redirect('/games');
    }

    /**
     * Print game details
     */
    public function print(string $id): void
    {
        require_once SRC_PATH . '/models/Game.php';

        $game = Game::findWithRelations((int)$id);

        if (!$game) {
            Session::setFlash('error', 'Spiel nicht gefunden.');
            $this->redirect('/games');
            return;
        }

        $this->setTitle($game['name'] . ' - Druckansicht');
        $this->setLayout('print');

        $this->render('games/print', [
            'game' => $game,
        ]);
    }

    /**
     * Duplicate a game
     */
    public function duplicate(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $game = Game::find((int)$id);

        if (!$game) {
            Session::setFlash('error', 'Spiel nicht gefunden.');
            $this->redirect('/games');
            return;
        }

        $newGameId = Game::duplicate((int)$id);

        if ($newGameId) {
            $newGame = Game::find($newGameId);
            ChangelogService::getInstance()->logCreate('game', $newGameId, $newGame['name'], ['duplicated_from' => $game['name']]);

            Session::setFlash('success', 'Spiel wurde dupliziert.');
            $this->redirect('/games/' . $newGameId . '/edit');
        } else {
            Session::setFlash('error', 'Fehler beim Duplizieren des Spiels.');
            $this->redirect('/games/' . $id);
        }
    }

    /**
     * Parse materials from form input
     */
    private function parseMaterials(array $materialsInput): array
    {
        $materials = [];
        if (is_array($materialsInput)) {
            foreach ($materialsInput as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $materials[] = [
                        'id' => (int)$item['id'],
                        'quantity' => isset($item['quantity']) ? (int)$item['quantity'] : 1,
                    ];
                } elseif (is_numeric($item)) {
                    $materials[] = ['id' => (int)$item, 'quantity' => 1];
                }
            }
        }
        return $materials;
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
