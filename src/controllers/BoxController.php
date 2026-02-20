<?php
/**
 * Box Controller
 */

class BoxController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * List all boxes
     */
    public function index(): void
    {
        require_once SRC_PATH . '/models/Box.php';

        $orderBy = $this->getQuery('sort', 'name');
        $direction = strtoupper($this->getQuery('dir', 'ASC'));

        // Validate sort column
        $allowedSort = ['name', 'number', 'location', 'created_at'];
        if (!in_array($orderBy, $allowedSort)) {
            $orderBy = 'name';
        }
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }

        $boxes = Box::allWithMaterialCount($orderBy, $direction);

        $this->setTitle(__('box.title_plural'));
        $this->addBreadcrumb(__('box.title_plural'), url('/boxes'));

        $this->render('boxes/index', [
            'boxes' => $boxes,
            'currentSort' => $orderBy,
            'currentDir' => $direction,
        ]);
    }

    /**
     * Show single box
     */
    public function show(string $id): void
    {
        require_once SRC_PATH . '/models/Box.php';

        $box = Box::findWithMaterialCount((int)$id);

        if (!$box) {
            Session::setFlash('error', __('box.not_found'));
            $this->redirect('/boxes');
            return;
        }

        $materials = Box::getMaterials((int)$id);

        $this->setTitle($box['name']);
        $this->addBreadcrumb(__('box.title_plural'), url('/boxes'));
        $this->addBreadcrumb($box['name']);

        $this->render('boxes/show', [
            'box' => $box,
            'materials' => $materials,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        require_once SRC_PATH . '/models/Location.php';

        $this->setTitle(__('box.create'));
        $this->addBreadcrumb(__('box.title_plural'), url('/boxes'));
        $this->addBreadcrumb(__('box.create'));

        $this->render('boxes/form', [
            'box' => null,
            'isEdit' => false,
            'locations' => Location::getForSelect(),
        ]);
    }

    /**
     * Store new box
     */
    public function store(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Box.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $locationId = $this->getPost('location_id', '');
        $data = [
            'name' => trim($this->getPost('name', '')),
            'number' => trim($this->getPost('number', '')),
            'label' => trim($this->getPost('label', '')),
            'location_id' => $locationId !== '' ? (int)$locationId : null,
            'description' => trim($this->getPost('description', '')),
            'notes' => trim($this->getPost('notes', '')),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')),
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
            'number' => 'max:20',
            'label' => 'max:50',
        ]);

        // Check duplicate name
        if (!empty($data['name']) && Box::nameExists($data['name'])) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/boxes/create');
            return;
        }

        // Create box
        $boxId = Box::create($data);

        if (!$boxId) {
            Session::setFlash('error', __('flash.error_generic'));
            Session::setOldInput($data);
            $this->redirect('/boxes/create');
            return;
        }

        // Log change
        ChangelogService::getInstance()->logCreate('box', $boxId, $data['name'], $data);

        Session::setFlash('success', __('flash.created', ['item' => __('box.title')]));
        $this->redirect('/boxes/' . $boxId);
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        require_once SRC_PATH . '/models/Box.php';
        require_once SRC_PATH . '/models/Location.php';

        $box = Box::find((int)$id);

        if (!$box) {
            Session::setFlash('error', __('box.not_found'));
            $this->redirect('/boxes');
            return;
        }

        $this->setTitle(__('box.edit'));
        $this->addBreadcrumb(__('box.title_plural'), url('/boxes'));
        $this->addBreadcrumb($box['name'], url('/boxes/' . $id));
        $this->addBreadcrumb(__('action.edit'));

        $this->render('boxes/form', [
            'box' => $box,
            'isEdit' => true,
            'locations' => Location::getForSelect(),
        ]);
    }

    /**
     * Update box
     */
    public function update(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Box.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $box = Box::find((int)$id);

        if (!$box) {
            Session::setFlash('error', __('box.not_found'));
            $this->redirect('/boxes');
            return;
        }

        $locationId = $this->getPost('location_id', '');
        $data = [
            'name' => trim($this->getPost('name', '')),
            'number' => trim($this->getPost('number', '')),
            'label' => trim($this->getPost('label', '')),
            'location_id' => $locationId !== '' ? (int)$locationId : null,
            'description' => trim($this->getPost('description', '')),
            'notes' => trim($this->getPost('notes', '')),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')) ?: $box['image_path'],
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
            'number' => 'max:20',
            'label' => 'max:50',
        ]);

        // Check duplicate name (excluding current)
        if (!empty($data['name']) && Box::nameExists($data['name'], (int)$id)) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/boxes/' . $id . '/edit');
            return;
        }

        // Track changes for changelog
        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($box, $data, ['name', 'number', 'label', 'location_id', 'description', 'notes', 'image_path']);

        // Update box
        Box::update((int)$id, $data);

        // Log change if there were any
        $changelog->logUpdate('box', (int)$id, $data['name'], $changes);

        Session::setFlash('success', __('flash.updated', ['item' => __('box.title')]));
        $this->redirect('/boxes/' . $id);
    }

    /**
     * Delete box
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Box.php';
        require_once SRC_PATH . '/services/ChangelogService.php';
        require_once SRC_PATH . '/services/ImageProcessor.php';

        $box = Box::find((int)$id);

        if (!$box) {
            Session::setFlash('error', __('box.not_found'));
            $this->redirect('/boxes');
            return;
        }

        // Log change before deletion
        ChangelogService::getInstance()->logDelete('box', (int)$id, $box['name'], $box);

        // Delete box (materials will have box_id set to NULL due to foreign key)
        Box::delete((int)$id);

        // Delete image if exists
        if ($box['image_path']) {
            (new ImageProcessor())->delete($box['image_path']);
        }

        Session::setFlash('success', __('flash.deleted', ['item' => __('box.title')]));
        $this->redirect('/boxes');
    }

    /**
     * Print box contents
     */
    public function print(string $id): void
    {
        require_once SRC_PATH . '/models/Box.php';

        $box = Box::find((int)$id);

        if (!$box) {
            Session::setFlash('error', __('box.not_found'));
            $this->redirect('/boxes');
            return;
        }

        $materials = Box::getMaterials((int)$id);

        $this->setLayout('print');
        $this->setTitle($box['name']);

        $this->render('boxes/print', [
            'box' => $box,
            'materials' => $materials,
            'printTitle' => 'Box: ' . $box['name'],
        ]);
    }

}
