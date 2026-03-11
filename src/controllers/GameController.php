<?php
/**
 * Game Controller
 *
 * Thin HTTP adapter — all business logic lives in GameService.
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

        $service = new GameService();
        $result = $service->list($filters, $sort, $order);
        $filterOptions = $service->getFilterOptions();

        $this->setTitle(__('game.title_plural'));
        $this->addBreadcrumb(__('game.title_plural'), url('/games'));

        $this->render('games/index', [
            'games' => $result->data['games'],
            'boxes' => $filterOptions['boxes'],
            'categories' => $filterOptions['categories'],
            'tags' => $filterOptions['tags'],
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

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'instructions' => trim($this->getPost('instructions', '')),
            'min_players' => $this->getPost('min_players') ? (int)$this->getPost('min_players') : null,
            'max_players' => $this->getPost('max_players') ? (int)$this->getPost('max_players') : null,
            'duration_minutes' => $this->getPost('duration_minutes') ? (int)$this->getPost('duration_minutes') : null,
            'difficulty' => $this->getPost('difficulty') ? (int)$this->getPost('difficulty') : 1,
            'is_outdoor' => $this->getPost('is_outdoor') ? 1 : 0,
            'is_active' => $this->getPost('is_active') ? 1 : 0,
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')),
            'box_id' => $this->getPost('box_id') ? (int)$this->getPost('box_id') : null,
            'category_id' => $this->getPost('category_id') ? (int)$this->getPost('category_id') : null,
        ];

        $tagIds = $this->getPost('tags', []);
        $materials = GameService::parseMaterials($this->getPost('materials', []));

        $result = (new GameService())->create($data, $tagIds, $materials);

        if ($result->failed()) {
            Session::setErrors($result->errors);
            Session::setOldInput(array_merge($data, ['tags' => $tagIds, 'materials' => $materials]));
            if ($result->message) {
                Session::setFlash('error', $result->message);
            }
            $this->redirect('/games/create');
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/games/' . $result->data['id']);
    }

    /**
     * Show game details
     */
    public function show(string $id): void
    {
        $result = (new GameService())->get((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/games');
            return;
        }

        $game = $result->data['game'];
        $groups = Group::getForSelect();

        $this->setTitle($game['name']);
        $this->addBreadcrumb(__('game.title_plural'), url('/games'));
        $this->addBreadcrumb($game['name']);

        $this->render('games/show', [
            'game' => $game,
            'groups' => $groups,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        $result = (new GameService())->get((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/games');
            return;
        }

        $game = $result->data['game'];

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

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'instructions' => trim($this->getPost('instructions', '')),
            'min_players' => $this->getPost('min_players') ? (int)$this->getPost('min_players') : null,
            'max_players' => $this->getPost('max_players') ? (int)$this->getPost('max_players') : null,
            'duration_minutes' => $this->getPost('duration_minutes') ? (int)$this->getPost('duration_minutes') : null,
            'difficulty' => $this->getPost('difficulty') ? (int)$this->getPost('difficulty') : 1,
            'is_outdoor' => $this->getPost('is_outdoor') ? 1 : 0,
            'is_active' => $this->getPost('is_active') ? 1 : 0,
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')),
            'box_id' => $this->getPost('box_id') ? (int)$this->getPost('box_id') : null,
            'category_id' => $this->getPost('category_id') ? (int)$this->getPost('category_id') : null,
        ];

        // Preserve existing image if no new one provided
        if (empty($data['image_path'])) {
            $existing = Game::find((int)$id);
            $data['image_path'] = $existing ? $existing['image_path'] : '';
        }

        $tagIds = $this->getPost('tags', []);
        $materials = GameService::parseMaterials($this->getPost('materials', []));

        $result = (new GameService())->update((int)$id, $data, $tagIds, $materials);

        if ($result->failed()) {
            Session::setErrors($result->errors);
            Session::setOldInput(array_merge($data, ['tags' => $tagIds, 'materials' => $materials]));
            if ($result->message) {
                Session::setFlash('error', $result->message);
            }
            $this->redirect('/games/' . $id . '/edit');
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/games/' . $id);
    }

    /**
     * Delete game
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        $result = (new GameService())->delete((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/games/' . $id);
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/games');
    }

    /**
     * Print game details
     */
    public function print(string $id): void
    {
        $result = (new GameService())->get((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/games');
            return;
        }

        $game = $result->data['game'];

        $this->setTitle($game['name'] . ' - ' . __('print.print_view'));
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

        $result = (new GameService())->duplicate((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/games/' . $id);
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/games/' . $result->data['id'] . '/edit');
    }
}
