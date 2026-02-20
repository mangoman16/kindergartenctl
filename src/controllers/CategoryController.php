<?php
/**
 * Category Controller (Age Groups)
 */

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * List all categories
     */
    public function index(): void
    {
        require_once SRC_PATH . '/models/Category.php';

        $categories = Category::allWithGameCount('sort_order', 'ASC');

        $this->setTitle(__('category.title_plural'));
        $this->addBreadcrumb(__('category.title_plural'), url('/categories'));

        $this->render('categories/index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        require_once SRC_PATH . '/models/Category.php';

        $this->setTitle(__('category.create'));
        $this->addBreadcrumb(__('category.title_plural'), url('/categories'));
        $this->addBreadcrumb(__('category.create'));

        $this->render('categories/form', [
            'category' => null,
            'isEdit' => false,
            'nextSortOrder' => Category::getNextSortOrder(),
        ]);
    }

    /**
     * Store new category
     */
    public function store(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Category.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'sort_order' => (int)$this->getPost('sort_order', 0),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')),
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:50',
        ]);

        // Check duplicate name
        if (!empty($data['name']) && Category::nameExists($data['name'])) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/categories/create');
            return;
        }

        // Create category
        $categoryId = Category::create($data);

        if (!$categoryId) {
            Session::setFlash('error', __('flash.error_generic'));
            Session::setOldInput($data);
            $this->redirect('/categories/create');
            return;
        }

        // Log change
        ChangelogService::getInstance()->logCreate('category', $categoryId, $data['name'], $data);

        Session::setFlash('success', __('flash.created', ['item' => __('category.title')]));
        $this->redirect('/categories');
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        require_once SRC_PATH . '/models/Category.php';

        $category = Category::find((int)$id);

        if (!$category) {
            Session::setFlash('error', __('category.not_found'));
            $this->redirect('/categories');
            return;
        }

        $this->setTitle(__('category.edit'));
        $this->addBreadcrumb(__('category.title_plural'), url('/categories'));
        $this->addBreadcrumb(__('action.edit'));

        $this->render('categories/form', [
            'category' => $category,
            'isEdit' => true,
        ]);
    }

    /**
     * Update category
     */
    public function update(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Category.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $category = Category::find((int)$id);

        if (!$category) {
            Session::setFlash('error', __('category.not_found'));
            $this->redirect('/categories');
            return;
        }

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'sort_order' => (int)$this->getPost('sort_order', 0),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')) ?: $category['image_path'],
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:50',
        ]);

        // Check duplicate name (excluding current)
        if (!empty($data['name']) && Category::nameExists($data['name'], (int)$id)) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/categories/' . $id . '/edit');
            return;
        }

        // Track changes for changelog
        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($category, $data, ['name', 'description', 'sort_order', 'image_path']);

        // Update category
        Category::update((int)$id, $data);

        // Log change if there were any
        $changelog->logUpdate('category', (int)$id, $data['name'], $changes);

        Session::setFlash('success', __('flash.updated', ['item' => __('category.title')]));
        $this->redirect('/categories');
    }

    /**
     * Print category games list
     */
    public function print(string $id): void
    {
        require_once SRC_PATH . '/models/Category.php';
        require_once SRC_PATH . '/models/Game.php';

        $category = Category::findWithGameCount((int)$id);

        if (!$category) {
            Session::setFlash('error', __('category.not_found'));
            $this->redirect('/categories');
            return;
        }

        // Get all games for this category
        $games = Category::getGames((int)$id);

        $this->setLayout('print');
        $this->render('categories/print', [
            'category' => $category,
            'games' => $games,
            'printTitle' => __('print.game_list') . ': ' . $category['name'],
        ]);
    }

    /**
     * Delete category
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Category.php';
        require_once SRC_PATH . '/services/ChangelogService.php';
        require_once SRC_PATH . '/services/ImageProcessor.php';

        $category = Category::find((int)$id);

        if (!$category) {
            Session::setFlash('error', __('category.not_found'));
            $this->redirect('/categories');
            return;
        }

        // Log change before deletion
        ChangelogService::getInstance()->logDelete('category', (int)$id, $category['name'], $category);

        // Delete category (game_categories entries will be deleted due to foreign key)
        Category::delete((int)$id);

        // Delete image if exists
        if ($category['image_path']) {
            (new ImageProcessor())->delete($category['image_path']);
        }

        Session::setFlash('success', __('flash.deleted', ['item' => __('category.title')]));
        $this->redirect('/categories');
    }
}
