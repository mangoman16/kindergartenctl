<?php
/**
 * Material Controller
 *
 * Thin HTTP adapter — all business logic lives in MaterialService.
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
        $filters = [
            'is_favorite' => $this->getQuery('favorites') !== null ? (int)$this->getQuery('favorites') : null,
            'search' => $this->getQuery('q') ?: null,
        ];

        $sort = $this->getQuery('sort', 'name');
        $order = $this->getQuery('order', 'asc');

        $result = (new MaterialService())->list($sort, $order, $filters);

        $this->setTitle(__('material.title_plural'));
        $this->addBreadcrumb(__('material.title_plural'), url('/materials'));

        $this->render('materials/index', [
            'materials' => $result->data['materials'],
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

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')),
            'quantity' => (int)$this->getPost('quantity', 0),
            'is_consumable' => $this->getPost('is_consumable') ? 1 : 0,
        ];

        $result = (new MaterialService())->create($data);

        if ($result->failed()) {
            Session::setErrors($result->errors);
            Session::setOldInput($data);
            if ($result->message) {
                Session::setFlash('error', $result->message);
            }
            $this->redirect('/materials/create');
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/materials/' . $result->data['id']);
    }

    /**
     * Show material details
     */
    public function show(string $id): void
    {
        $result = (new MaterialService())->get((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/materials');
            return;
        }

        $material = $result->data['material'];
        $games = $result->data['games'];
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
        $material = Material::find((int)$id);

        if (!$material) {
            Session::setFlash('error', __('material.not_found'));
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

        $material = Material::find((int)$id);

        if (!$material) {
            Session::setFlash('error', __('material.not_found'));
            $this->redirect('/materials');
            return;
        }

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')) ?: $material['image_path'],
            'quantity' => (int)$this->getPost('quantity', 0),
            'is_consumable' => $this->getPost('is_consumable') ? 1 : 0,
        ];

        $result = (new MaterialService())->update((int)$id, $data);

        if ($result->failed()) {
            Session::setErrors($result->errors);
            Session::setOldInput($data);
            if ($result->message) {
                Session::setFlash('error', $result->message);
            }
            $this->redirect('/materials/' . $id . '/edit');
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/materials/' . $id);
    }

    /**
     * Delete material
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        $result = (new MaterialService())->delete((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/materials');
            return;
        }

        Session::setFlash('success', $result->message);
        $this->redirect('/materials');
    }

    /**
     * Print material details
     */
    public function print(string $id): void
    {
        $result = (new MaterialService())->get((int)$id);

        if ($result->failed()) {
            Session::setFlash('error', $result->message);
            $this->redirect('/materials');
            return;
        }

        $material = $result->data['material'];
        $games = $result->data['games'];

        $this->setTitle($material['name'] . ' - ' . __('print.print_view'));
        $this->setLayout('print');

        $this->render('materials/print', [
            'material' => $material,
            'games' => $games,
        ]);
    }
}
