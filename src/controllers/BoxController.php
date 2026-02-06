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
        $direction = $this->getQuery('dir', 'ASC');

        // Validate sort column
        $allowedSort = ['name', 'number', 'location', 'created_at'];
        if (!in_array($orderBy, $allowedSort)) {
            $orderBy = 'name';
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
            Session::setFlash('error', 'Box nicht gefunden.');
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
        $this->setTitle(__('box.create'));
        $this->addBreadcrumb(__('box.title_plural'), url('/boxes'));
        $this->addBreadcrumb(__('box.create'));

        $this->render('boxes/form', [
            'box' => null,
            'isEdit' => false,
        ]);
    }

    /**
     * Store new box
     */
    public function store(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Box.php';

        $data = [
            'name' => trim($this->getPost('name', '')),
            'number' => trim($this->getPost('number', '')),
            'location' => trim($this->getPost('location', '')),
            'description' => trim($this->getPost('description', '')),
            'notes' => trim($this->getPost('notes', '')),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')),
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
            'number' => 'max:20',
            'location' => 'max:255',
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
            Session::setFlash('error', 'Fehler beim Erstellen der Box.');
            Session::setOldInput($data);
            $this->redirect('/boxes/create');
            return;
        }

        // Log change
        $this->logChange('box', $boxId, $data['name'], 'create', $data);

        Session::setFlash('success', __('flash.created', ['item' => __('box.title')]));
        $this->redirect('/boxes/' . $boxId);
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        require_once SRC_PATH . '/models/Box.php';

        $box = Box::find((int)$id);

        if (!$box) {
            Session::setFlash('error', 'Box nicht gefunden.');
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
        ]);
    }

    /**
     * Update box
     */
    public function update(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Box.php';

        $box = Box::find((int)$id);

        if (!$box) {
            Session::setFlash('error', 'Box nicht gefunden.');
            $this->redirect('/boxes');
            return;
        }

        $data = [
            'name' => trim($this->getPost('name', '')),
            'number' => trim($this->getPost('number', '')),
            'location' => trim($this->getPost('location', '')),
            'description' => trim($this->getPost('description', '')),
            'notes' => trim($this->getPost('notes', '')),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')) ?: $box['image_path'],
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
            'number' => 'max:20',
            'location' => 'max:255',
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
        $changes = $this->getChanges($box, $data);

        // Update box
        Box::update((int)$id, $data);

        // Log change if there were any
        if (!empty($changes)) {
            $this->logChange('box', (int)$id, $data['name'], 'update', $changes);
        }

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

        $box = Box::find((int)$id);

        if (!$box) {
            Session::setFlash('error', 'Box nicht gefunden.');
            $this->redirect('/boxes');
            return;
        }

        // Log change before deletion
        $this->logChange('box', (int)$id, $box['name'], 'delete', $box);

        // Delete box (materials will have box_id set to NULL due to foreign key)
        Box::delete((int)$id);

        // Delete image if exists
        if ($box['image_path']) {
            $this->deleteImage($box['image_path']);
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
            Session::setFlash('error', 'Box nicht gefunden.');
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

    /**
     * Log a change to the changelog
     */
    private function logChange(string $entityType, int $entityId, string $entityName, string $action, array $data): void
    {
        try {
            $db = Database::getInstance();
            $userId = Auth::id();

            $stmt = $db->prepare("
                INSERT INTO changelog (user_id, entity_type, entity_id, entity_name, action, changes)
                VALUES (:user_id, :entity_type, :entity_id, :entity_name, :action, :changes)
            ");

            $stmt->execute([
                'user_id' => $userId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'entity_name' => $entityName,
                'action' => $action,
                'changes' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            Logger::error('Failed to log change', [
                'error' => $e->getMessage(),
                'entity_type' => $entityType,
                'entity_id' => $entityId
            ]);
        }
    }

    /**
     * Get changes between old and new data
     */
    private function getChanges(array $old, array $new): array
    {
        $changes = [];
        $trackFields = ['name', 'number', 'location', 'description', 'notes', 'image_path'];

        foreach ($trackFields as $field) {
            $oldValue = $old[$field] ?? '';
            $newValue = $new[$field] ?? '';

            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Delete an image file
     */
    private function deleteImage(string $path): void
    {
        $fullPath = UPLOADS_PATH . '/' . $path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Also delete thumbnail
        $thumbPath = str_replace('/full/', '/thumbs/', $fullPath);
        if (file_exists($thumbPath)) {
            unlink($thumbPath);
        }
    }
}
