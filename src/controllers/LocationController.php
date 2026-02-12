<?php
/**
 * Location Controller - Manages predefined storage locations (Standorte)
 */

class LocationController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * List all locations
     */
    public function index(): void
    {
        require_once SRC_PATH . '/models/Location.php';

        $orderBy = $this->getQuery('sort', 'name');
        $direction = $this->getQuery('dir', 'ASC');

        $allowedSort = ['name', 'created_at'];
        if (!in_array($orderBy, $allowedSort)) {
            $orderBy = 'name';
        }

        $locations = Location::allWithBoxCount($orderBy, $direction);

        $this->setTitle(__('location.title_plural'));
        $this->addBreadcrumb(__('location.title_plural'), url('/locations'));

        $this->render('locations/index', [
            'locations' => $locations,
            'currentSort' => $orderBy,
            'currentDir' => $direction,
        ]);
    }

    /**
     * Show single location
     */
    public function show(string $id): void
    {
        require_once SRC_PATH . '/models/Location.php';

        $location = Location::findWithBoxCount((int)$id);

        if (!$location) {
            Session::setFlash('error', __('location.not_found'));
            $this->redirect('/locations');
            return;
        }

        $boxes = Location::getBoxes((int)$id);

        $this->setTitle($location['name']);
        $this->addBreadcrumb(__('location.title_plural'), url('/locations'));
        $this->addBreadcrumb($location['name']);

        $this->render('locations/show', [
            'location' => $location,
            'boxes' => $boxes,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->setTitle(__('location.create'));
        $this->addBreadcrumb(__('location.title_plural'), url('/locations'));
        $this->addBreadcrumb(__('location.create'));

        $this->render('locations/form', [
            'location' => null,
            'isEdit' => false,
        ]);
    }

    /**
     * Store new location
     */
    public function store(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Location.php';

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
        ];

        $validator = Validator::make($data, [
            'name' => 'required|max:150',
        ]);

        if (!empty($data['name']) && Location::nameExists($data['name'])) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/locations/create');
            return;
        }

        $locationId = Location::create($data);

        if (!$locationId) {
            Session::setFlash('error', __('flash.error'));
            Session::setOldInput($data);
            $this->redirect('/locations/create');
            return;
        }

        $this->logChange('location', $locationId, $data['name'], 'create', $data);

        Session::setFlash('success', __('flash.created', ['item' => __('location.title')]));
        $this->redirect('/locations/' . $locationId);
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        require_once SRC_PATH . '/models/Location.php';

        $location = Location::find((int)$id);

        if (!$location) {
            Session::setFlash('error', __('location.not_found'));
            $this->redirect('/locations');
            return;
        }

        $this->setTitle(__('location.edit'));
        $this->addBreadcrumb(__('location.title_plural'), url('/locations'));
        $this->addBreadcrumb($location['name'], url('/locations/' . $id));
        $this->addBreadcrumb(__('action.edit'));

        $this->render('locations/form', [
            'location' => $location,
            'isEdit' => true,
        ]);
    }

    /**
     * Update location
     */
    public function update(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Location.php';

        $location = Location::find((int)$id);

        if (!$location) {
            Session::setFlash('error', __('location.not_found'));
            $this->redirect('/locations');
            return;
        }

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
        ];

        $validator = Validator::make($data, [
            'name' => 'required|max:150',
        ]);

        if (!empty($data['name']) && Location::nameExists($data['name'], (int)$id)) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/locations/' . $id . '/edit');
            return;
        }

        $changes = $this->getChanges($location, $data);

        Location::update((int)$id, $data);

        if (!empty($changes)) {
            $this->logChange('location', (int)$id, $data['name'], 'update', $changes);
        }

        Session::setFlash('success', __('flash.updated', ['item' => __('location.title')]));
        $this->redirect('/locations/' . $id);
    }

    /**
     * Delete location
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Location.php';

        $location = Location::find((int)$id);

        if (!$location) {
            Session::setFlash('error', __('location.not_found'));
            $this->redirect('/locations');
            return;
        }

        $this->logChange('location', (int)$id, $location['name'], 'delete', $location);

        Location::delete((int)$id);

        Session::setFlash('success', __('flash.deleted', ['item' => __('location.title')]));
        $this->redirect('/locations');
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
        $trackFields = ['name', 'description'];

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
}
