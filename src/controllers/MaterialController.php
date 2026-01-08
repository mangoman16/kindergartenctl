<?php
/**
 * Material Controller
 */

class MaterialController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * List all materials
     */
    public function index(): void
    {
        require_once SRC_PATH . '/models/Material.php';

        $filters = [
            'is_favorite' => $this->getQuery('favorites') !== null ? (int)$this->getQuery('favorites') : null,
            'search' => $this->getQuery('q') ?: null,
        ];

        $sort = $this->getQuery('sort', 'name');
        $order = $this->getQuery('order', 'asc');

        $materials = Material::allWithGameCount($sort, $order, $filters);

        $this->setTitle(__('material.title_plural'));
        $this->addBreadcrumb(__('material.title_plural'), url('/materials'));

        $this->render('materials/index', [
            'materials' => $materials,
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
        $this->setTitle(__('material.create'));
        $this->addBreadcrumb(__('material.title_plural'), url('/materials'));
        $this->addBreadcrumb(__('material.create'));

        $this->render('materials/form', [
            'material' => null,
            'isEdit' => false,
        ]);
    }

    /**
     * Store new material
     */
    public function store(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Material.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'image_path' => $this->getPost('image_path', ''),
            'quantity' => (int)$this->getPost('quantity', 0),
            'is_consumable' => $this->getPost('is_consumable') ? 1 : 0,
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
        ]);

        // Check duplicate name
        if (!empty($data['name']) && Material::nameExists($data['name'])) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/materials/create');
            return;
        }

        // Create material
        $materialId = Material::create($data);

        if (!$materialId) {
            Session::setFlash('error', 'Fehler beim Erstellen des Materials.');
            Session::setOldInput($data);
            $this->redirect('/materials/create');
            return;
        }

        // Log change
        ChangelogService::getInstance()->logCreate('material', $materialId, $data['name'], $data);

        Session::setFlash('success', __('flash.created', ['item' => __('material.title')]));
        $this->redirect('/materials');
    }

    /**
     * Show material details
     */
    public function show(string $id): void
    {
        require_once SRC_PATH . '/models/Material.php';
        require_once SRC_PATH . '/models/Group.php';

        $material = Material::findWithGameCount((int)$id);

        if (!$material) {
            Session::setFlash('error', 'Material nicht gefunden.');
            $this->redirect('/materials');
            return;
        }

        $games = Material::getGames((int)$id);
        $groups = Group::getForSelect();

        $this->setTitle($material['name']);
        $this->addBreadcrumb(__('material.title_plural'), url('/materials'));
        $this->addBreadcrumb($material['name']);

        $this->render('materials/show', [
            'material' => $material,
            'games' => $games,
            'groups' => $groups,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        require_once SRC_PATH . '/models/Material.php';

        $material = Material::find((int)$id);

        if (!$material) {
            Session::setFlash('error', 'Material nicht gefunden.');
            $this->redirect('/materials');
            return;
        }

        $this->setTitle(__('material.edit'));
        $this->addBreadcrumb(__('material.title_plural'), url('/materials'));
        $this->addBreadcrumb(__('action.edit'));

        $this->render('materials/form', [
            'material' => $material,
            'isEdit' => true,
        ]);
    }

    /**
     * Update material
     */
    public function update(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Material.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $material = Material::find((int)$id);

        if (!$material) {
            Session::setFlash('error', 'Material nicht gefunden.');
            $this->redirect('/materials');
            return;
        }

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'image_path' => $this->getPost('image_path', '') ?: $material['image_path'],
            'quantity' => (int)$this->getPost('quantity', 0),
            'is_consumable' => $this->getPost('is_consumable') ? 1 : 0,
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
        ]);

        // Check duplicate name (excluding current)
        if (!empty($data['name']) && Material::nameExists($data['name'], (int)$id)) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/materials/' . $id . '/edit');
            return;
        }

        // Track changes
        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($material, $data, ['name', 'description', 'image_path', 'quantity', 'is_consumable']);

        // Update material
        Material::update((int)$id, $data);

        // Log change
        if (!empty($changes)) {
            $changelog->logUpdate('material', (int)$id, $data['name'], $changes);
        }

        Session::setFlash('success', __('flash.updated', ['item' => __('material.title')]));
        $this->redirect('/materials');
    }

    /**
     * Delete material
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Material.php';
        require_once SRC_PATH . '/services/ChangelogService.php';
        require_once SRC_PATH . '/services/ImageProcessor.php';

        $material = Material::find((int)$id);

        if (!$material) {
            Session::setFlash('error', 'Material nicht gefunden.');
            $this->redirect('/materials');
            return;
        }

        // Log change before deletion
        ChangelogService::getInstance()->logDelete('material', (int)$id, $material['name'], $material);

        // Delete material (game_materials entries will be deleted due to foreign key)
        Material::delete((int)$id);

        // Delete image if exists
        if ($material['image_path']) {
            $processor = new ImageProcessor();
            $processor->delete($material['image_path']);
        }

        Session::setFlash('success', __('flash.deleted', ['item' => __('material.title')]));
        $this->redirect('/materials');
    }

    /**
     * Print material details
     */
    public function print(string $id): void
    {
        require_once SRC_PATH . '/models/Material.php';

        $material = Material::findWithGameCount((int)$id);

        if (!$material) {
            Session::setFlash('error', 'Material nicht gefunden.');
            $this->redirect('/materials');
            return;
        }

        $games = Material::getGames((int)$id);

        $this->setTitle($material['name'] . ' - Druckansicht');
        $this->setLayout('print');

        $this->render('materials/print', [
            'material' => $material,
            'games' => $games,
        ]);
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
