<?php
/**
 * Group Controller
 */

class GroupController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * List all groups
     */
    public function index(): void
    {
        require_once SRC_PATH . '/models/Group.php';

        $groups = Group::allWithCounts();

        $this->setTitle(__('group.title_plural'));
        $this->addBreadcrumb(__('group.title_plural'), url('/groups'));

        $this->render('groups/index', [
            'groups' => $groups,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/models/Material.php';

        $this->setTitle(__('group.create'));
        $this->addBreadcrumb(__('group.title_plural'), url('/groups'));
        $this->addBreadcrumb(__('group.create'));

        $this->render('groups/form', [
            'group' => null,
            'isEdit' => false,
            'games' => Game::getForSelect(),
            'materials' => Material::getForSelect(),
            'selectedGames' => [],
            'selectedMaterials' => [],
        ]);
    }

    /**
     * Store new group
     */
    public function store(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Group.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')),
        ];

        $gameIds = $this->getPost('games', []);
        $materials = $this->parseMaterials($this->getPost('materials', []));

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
        ]);

        // Check duplicate name
        if (!empty($data['name']) && Group::nameExists($data['name'])) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput(array_merge($data, ['games' => $gameIds, 'materials' => $materials]));
            $this->redirect('/groups/create');
            return;
        }

        // Create group
        $groupId = Group::create($data);

        if (!$groupId) {
            Session::setFlash('error', __('flash.error_creating'));
            Session::setOldInput(array_merge($data, ['games' => $gameIds, 'materials' => $materials]));
            $this->redirect('/groups/create');
            return;
        }

        // Update games and materials
        Group::updateGames($groupId, $gameIds);
        Group::updateMaterials($groupId, $materials);

        // Log change
        ChangelogService::getInstance()->logCreate('group', $groupId, $data['name'], $data);

        Session::setFlash('success', __('flash.created', ['item' => __('group.title')]));
        $this->redirect('/groups/' . $groupId);
    }

    /**
     * Show group details
     */
    public function show(string $id): void
    {
        require_once SRC_PATH . '/models/Group.php';

        $group = Group::findWithCounts((int)$id);

        if (!$group) {
            Session::setFlash('error', __('group.not_found'));
            $this->redirect('/groups');
            return;
        }

        $games = Group::getGames((int)$id);
        $materials = Group::getMaterials((int)$id);

        $this->setTitle($group['name']);
        $this->addBreadcrumb(__('group.title_plural'), url('/groups'));
        $this->addBreadcrumb($group['name']);

        $this->render('groups/show', [
            'group' => $group,
            'games' => $games,
            'materials' => $materials,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        require_once SRC_PATH . '/models/Group.php';
        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/models/Material.php';

        $group = Group::find((int)$id);

        if (!$group) {
            Session::setFlash('error', __('group.not_found'));
            $this->redirect('/groups');
            return;
        }

        $selectedGames = Group::getGames((int)$id);
        $selectedMaterials = Group::getMaterials((int)$id);

        $this->setTitle(__('group.edit'));
        $this->addBreadcrumb(__('group.title_plural'), url('/groups'));
        $this->addBreadcrumb($group['name'], url('/groups/' . $id));
        $this->addBreadcrumb(__('action.edit'));

        $this->render('groups/form', [
            'group' => $group,
            'isEdit' => true,
            'games' => Game::getForSelect(),
            'materials' => Material::getForSelect(),
            'selectedGames' => $selectedGames,
            'selectedMaterials' => $selectedMaterials,
        ]);
    }

    /**
     * Update group
     */
    public function update(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Group.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $group = Group::find((int)$id);

        if (!$group) {
            Session::setFlash('error', __('group.not_found'));
            $this->redirect('/groups');
            return;
        }

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')) ?: $group['image_path'],
        ];

        $gameIds = $this->getPost('games', []);
        $materials = $this->parseMaterials($this->getPost('materials', []));

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
        ]);

        // Check duplicate name (excluding current)
        if (!empty($data['name']) && Group::nameExists($data['name'], (int)$id)) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput(array_merge($data, ['games' => $gameIds, 'materials' => $materials]));
            $this->redirect('/groups/' . $id . '/edit');
            return;
        }

        // Track changes
        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($group, $data, ['name', 'description', 'image_path']);

        // Update group
        Group::update((int)$id, $data);

        // Update games and materials
        Group::updateGames((int)$id, $gameIds);
        Group::updateMaterials((int)$id, $materials);

        // Log change
        if (!empty($changes)) {
            $changelog->logUpdate('group', (int)$id, $data['name'], $changes);
        }

        Session::setFlash('success', __('flash.updated', ['item' => __('group.title')]));
        $this->redirect('/groups/' . $id);
    }

    /**
     * Delete group
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Group.php';
        require_once SRC_PATH . '/services/ChangelogService.php';
        require_once SRC_PATH . '/services/ImageProcessor.php';

        $group = Group::find((int)$id);

        if (!$group) {
            Session::setFlash('error', __('group.not_found'));
            $this->redirect('/groups');
            return;
        }

        // Log change before deletion
        ChangelogService::getInstance()->logDelete('group', (int)$id, $group['name'], $group);

        // Delete group (items will be deleted due to foreign key)
        Group::delete((int)$id);

        // Delete image if exists
        if ($group['image_path']) {
            $processor = new ImageProcessor();
            $processor->delete($group['image_path']);
        }

        Session::setFlash('success', __('flash.deleted', ['item' => __('group.title')]));
        $this->redirect('/groups');
    }

    /**
     * Print group contents
     */
    public function print(string $id): void
    {
        require_once SRC_PATH . '/models/Group.php';

        $group = Group::findWithCounts((int)$id);

        if (!$group) {
            Session::setFlash('error', __('group.not_found'));
            $this->redirect('/groups');
            return;
        }

        $games = Group::getGames((int)$id);
        $materials = Group::getMaterials((int)$id);

        $this->setLayout('print');
        $this->render('groups/print', [
            'group' => $group,
            'games' => $games,
            'materials' => $materials,
            'printTitle' => __('group.title') . ': ' . $group['name'],
        ]);
    }

    /**
     * Print preparation checklist (materials grouped by box)
     */
    public function printChecklist(string $id): void
    {
        require_once SRC_PATH . '/models/Group.php';

        $group = Group::findWithCounts((int)$id);

        if (!$group) {
            Session::setFlash('error', __('group.not_found'));
            $this->redirect('/groups');
            return;
        }

        $materials = Group::getMaterials((int)$id);

        // Group materials by box
        $materialsByBox = [];
        $noBoxMaterials = [];

        foreach ($materials as $material) {
            if ($material['box_id']) {
                $boxKey = $material['box_id'];
                if (!isset($materialsByBox[$boxKey])) {
                    $materialsByBox[$boxKey] = [
                        'box_name' => $material['box_name'] ?? __('misc.unknown_box'),
                        'materials' => [],
                    ];
                }
                $materialsByBox[$boxKey]['materials'][] = $material;
            } else {
                $noBoxMaterials[] = $material;
            }
        }

        // Sort boxes by name
        uasort($materialsByBox, fn($a, $b) => strcasecmp($a['box_name'], $b['box_name']));

        $this->setLayout('print');
        $this->render('groups/print-checklist', [
            'group' => $group,
            'materialsByBox' => $materialsByBox,
            'noBoxMaterials' => $noBoxMaterials,
            'totalMaterials' => count($materials),
            'printTitle' => __('print.preparation_list') . ': ' . $group['name'],
        ]);
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
}
