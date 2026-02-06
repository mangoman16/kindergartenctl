<?php
/**
 * Tag Controller (Themes)
 */

class TagController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * List all tags
     */
    public function index(): void
    {
        require_once SRC_PATH . '/models/Tag.php';

        $tags = Tag::allWithGameCount('name', 'ASC');

        $this->setTitle(__('tag.title_plural'));
        $this->addBreadcrumb(__('tag.title_plural'), url('/tags'));

        $this->render('tags/index', [
            'tags' => $tags,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->setTitle(__('tag.create'));
        $this->addBreadcrumb(__('tag.title_plural'), url('/tags'));
        $this->addBreadcrumb(__('tag.create'));

        $this->render('tags/form', [
            'tag' => null,
            'isEdit' => false,
        ]);
    }

    /**
     * Store new tag
     */
    public function store(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Tag.php';

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'color' => $this->getPost('color', ''),
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')),
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
        ]);

        // Check duplicate name
        if (!empty($data['name']) && Tag::nameExists($data['name'])) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/tags/create');
            return;
        }

        // Create tag
        $tagId = Tag::create($data);

        if (!$tagId) {
            Session::setFlash('error', 'Fehler beim Erstellen des Themas.');
            Session::setOldInput($data);
            $this->redirect('/tags/create');
            return;
        }

        // Log change
        $this->logChange('tag', $tagId, $data['name'], 'create', $data);

        Session::setFlash('success', __('flash.created', ['item' => __('tag.title')]));
        $this->redirect('/tags');
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        require_once SRC_PATH . '/models/Tag.php';

        $tag = Tag::find((int)$id);

        if (!$tag) {
            Session::setFlash('error', 'Thema nicht gefunden.');
            $this->redirect('/tags');
            return;
        }

        $this->setTitle(__('tag.edit'));
        $this->addBreadcrumb(__('tag.title_plural'), url('/tags'));
        $this->addBreadcrumb(__('action.edit'));

        $this->render('tags/form', [
            'tag' => $tag,
            'isEdit' => true,
        ]);
    }

    /**
     * Update tag
     */
    public function update(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Tag.php';

        $tag = Tag::find((int)$id);

        if (!$tag) {
            Session::setFlash('error', 'Thema nicht gefunden.');
            $this->redirect('/tags');
            return;
        }

        $data = [
            'name' => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'color' => $this->getPost('color', '') ?: $tag['color'],
            'image_path' => $this->sanitizeImagePath($this->getPost('image_path', '')) ?: $tag['image_path'],
        ];

        // Validate
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
        ]);

        // Check duplicate name (excluding current)
        if (!empty($data['name']) && Tag::nameExists($data['name'], (int)$id)) {
            $validator->addError('name', __('validation.duplicate'));
        }

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/tags/' . $id . '/edit');
            return;
        }

        // Track changes
        $changes = $this->getChanges($tag, $data);

        // Update tag
        Tag::update((int)$id, $data);

        // Log change
        if (!empty($changes)) {
            $this->logChange('tag', (int)$id, $data['name'], 'update', $changes);
        }

        Session::setFlash('success', __('flash.updated', ['item' => __('tag.title')]));
        $this->redirect('/tags');
    }

    /**
     * Delete tag
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/Tag.php';

        $tag = Tag::find((int)$id);

        if (!$tag) {
            Session::setFlash('error', 'Thema nicht gefunden.');
            $this->redirect('/tags');
            return;
        }

        // Log change before deletion
        $this->logChange('tag', (int)$id, $tag['name'], 'delete', $tag);

        // Delete tag (game_tags entries will be deleted due to foreign key)
        Tag::delete((int)$id);

        // Delete image if exists
        if ($tag['image_path']) {
            $this->deleteImage($tag['image_path']);
        }

        Session::setFlash('success', __('flash.deleted', ['item' => __('tag.title')]));
        $this->redirect('/tags');
    }

    /**
     * Print tag games list
     */
    public function print(string $id): void
    {
        require_once SRC_PATH . '/models/Tag.php';
        require_once SRC_PATH . '/models/Game.php';

        $tag = Tag::findWithGameCount((int)$id);

        if (!$tag) {
            Session::setFlash('error', 'Thema nicht gefunden.');
            $this->redirect('/tags');
            return;
        }

        // Get all games for this tag
        $games = Tag::getGames((int)$id);

        $this->setLayout('print');
        $this->render('tags/print', [
            'tag' => $tag,
            'games' => $games,
            'printTitle' => 'Spieleliste: ' . $tag['name'],
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
        $trackFields = ['name', 'description', 'color', 'image_path'];

        foreach ($trackFields as $field) {
            $oldValue = $old[$field] ?? '';
            $newValue = $new[$field] ?? '';

            if ((string)$oldValue !== (string)$newValue) {
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

        $thumbPath = str_replace('/full/', '/thumbs/', $fullPath);
        if (file_exists($thumbPath)) {
            unlink($thumbPath);
        }
    }
}
