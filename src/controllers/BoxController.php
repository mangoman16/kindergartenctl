<?php
/**
 * Box Controller
 *
 * Thin HTTP adapter — all business logic lives in BoxService.
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
        $orderBy = $this->getQuery('sort', 'name');
        $direction = strtoupper($this->getQuery('dir', 'ASC'));

        $result = (new BoxService())->list($orderBy, $direction);

        $this->setTitle(__('box.title_plural'));
        $this->addBreadcrumb(__('box.title_plural'), url('/boxes'));

        $this->render('boxes/index', [
            'boxes' => $result->data['boxes'],
            'currentSort' => $orderBy,
            'currentDir' => $direction,
        ]);
    }

    /**
     * Show single box
     */
    public function show(string $id): void
    {
        $result = (new BoxService())->get((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/boxes');
            return;
        }

        $box = $result->data['box'];
        $materials = $result->data['materials'];

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
            'locations' => Location::getForSelect(),
        ]);
    }

    /**
     * Store new box
     */
    public function store(): void
    {
        $this->requireCsrf();

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

        $result = (new BoxService())->create($data);

        if ($result->failed()) {
            Session::setErrors($result->errors);
            Session::setOldInput($data);
            if ($result->message) {
                Session::setFlash('error', $result->message);
            }
            $this->redirect('/boxes/create');
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/boxes/' . $result->data['id']);
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
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

        $result = (new BoxService())->update((int)$id, $data);

        if ($result->failed()) {
            Session::setErrors($result->errors);
            Session::setOldInput($data);
            if ($result->message) {
                Session::setFlash('error', $result->message);
            }
            $this->redirect('/boxes/' . $id . '/edit');
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/boxes/' . $id);
    }

    /**
     * Delete box
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        $result = (new BoxService())->delete((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/boxes');
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/boxes');
    }

    /**
     * Print box contents
     */
    public function print(string $id): void
    {
        $result = (new BoxService())->get((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/boxes');
            return;
        }

        $box = $result->data['box'];
        $materials = $result->data['materials'];

        $this->setLayout('print');
        $this->setTitle($box['name']);

        $this->render('boxes/print', [
            'box' => $box,
            'materials' => $materials,
            'printTitle' => 'Box: ' . $box['name'],
        ]);
    }
}
