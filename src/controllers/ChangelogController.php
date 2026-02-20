<?php
/**
 * Changelog Controller
 */

class ChangelogController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * List changelog entries
     */
    public function index(): void
    {
        require_once SRC_PATH . '/services/ChangelogService.php';

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $filterType = $_GET['type'] ?? '';
        $filterAction = $_GET['action'] ?? '';

        $changelog = ChangelogService::getInstance();

        // Get entries based on filters
        if ($filterAction) {
            $entries = $changelog->getByAction($filterAction, $perPage * 10);
        } else {
            $entries = $changelog->getRecent($perPage * 10, 0);
        }

        // Apply type filter as post-filter (works with or without action filter)
        if ($filterType) {
            $entries = array_filter($entries, fn($e) => $e['entity_type'] === $filterType);
        }

        $total = count($entries);
        $entries = array_slice($entries, $offset, $perPage);
        $totalPages = ceil($total / $perPage);

        $this->setTitle(__('changelog.title'));
        $this->addBreadcrumb(__('changelog.title'));

        $this->render('changelog/index', [
            'entries' => $entries,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'filterType' => $filterType,
            'filterAction' => $filterAction,
        ]);
    }

    /**
     * Clear changelog (admin only)
     */
    public function clear(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/services/ChangelogService.php';

        $keepDays = (int)($_POST['keep_days'] ?? 365);
        $deleted = ChangelogService::getInstance()->cleanup($keepDays);

        Session::setFlash('success', __('changelog.entries_deleted', ['count' => $deleted]));
        $this->redirect('/changelog');
    }
}
