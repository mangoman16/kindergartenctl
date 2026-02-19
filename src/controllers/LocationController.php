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
        require_once SRC_PATH . '/services/ChangelogService.php';

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
            Session::setFlash('error', __('flash.error_generic'));
            Session::setOldInput($data);
            $this->redirect('/locations/create');
            return;
        }

        ChangelogService::getInstance()->logCreate('location', $locationId, $data['name'], $data);

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
        require_once SRC_PATH . '/services/ChangelogService.php';

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

        // Track changes for changelog
        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($location, $data, ['name', 'description']);

        Location::update((int)$id, $data);

        // Log change if there were any
        $changelog->logUpdate('location', (int)$id, $data['name'], $changes);

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
        require_once SRC_PATH . '/services/ChangelogService.php';

        $location = Location::find((int)$id);

        if (!$location) {
            Session::setFlash('error', __('location.not_found'));
            $this->redirect('/locations');
            return;
        }

        ChangelogService::getInstance()->logDelete('location', (int)$id, $location['name'], $location);

        Location::delete((int)$id);

        Session::setFlash('success', __('flash.deleted', ['item' => __('location.title')]));
        $this->redirect('/locations');
    }
}
