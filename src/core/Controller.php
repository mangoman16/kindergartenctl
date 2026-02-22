<?php
/**
 * =====================================================================================
 * BASE CONTROLLER - Request Handling and View Rendering
 * =====================================================================================
 *
 * PURPOSE:
 * This abstract class provides common functionality for all controllers including
 * view rendering, authentication, CSRF protection, and response helpers.
 *
 * EXTENDING THIS CLASS:
 * ```php
 * class GameController extends Controller
 * {
 *     public function __construct()
 *     {
 *         $this->requireAuth(); // All actions require login
 *     }
 *
 *     public function index(): void
 *     {
 *         $games = Game::all();
 *         $this->setTitle('Games');
 *         $this->render('games/index', ['games' => $games]);
 *     }
 * }
 * ```
 *
 * VIEW RENDERING:
 * - render('view/path', $data) - Renders view within layout
 * - renderPartial('view/path', $data) - Renders view without layout
 * - Views located in src/views/, layouts in src/views/layouts/
 * - Default layout is 'main' (src/views/layouts/main.php)
 *
 * AVAILABLE IN VIEWS:
 * - $content - The rendered view content (in layouts)
 * - $pageTitle - Set via setTitle()
 * - $breadcrumbs - Set via addBreadcrumb()
 * - $flash - Flash messages from Session
 * - $errors - Validation errors from Session
 * - $csrfToken - CSRF token for forms
 * - $lang - Translation array
 * - All data passed to render()
 *
 * CSRF PROTECTION:
 * ```php
 * public function store(): void
 * {
 *     $this->requireCsrf(); // Dies with 403 if invalid
 *     // ... process form
 * }
 * ```
 * In forms: <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
 * For AJAX: Set X-CSRF-TOKEN header
 *
 * AUTHENTICATION:
 * - requireAuth() - Redirects to /login if not authenticated
 * - Called in constructor for protected controllers
 *
 * REQUEST HELPERS:
 * - getPost('key', $default) - Get POST parameter
 * - getQuery('key', $default) - Get GET parameter
 * - getFile('key') - Get uploaded file
 * - isPost() / isAjax() - Check request type
 *
 * RESPONSE HELPERS:
 * - json($data, $statusCode) - Send JSON response and exit
 * - redirect('/path') - Redirect (validates URL)
 * - back() - Redirect to HTTP referer
 * - redirectWithMessage('/path', 'success', 'Message')
 *
 * LAYOUT OPTIONS:
 * - 'main' - Standard authenticated layout with nav
 * - 'auth' - Login/auth pages (minimal)
 * - 'print' - Print-friendly layout
 * - 'install' - Installation wizard layout
 *
 * CHILD CONTROLLERS:
 * - AuthController - Login, logout, password reset
 * - DashboardController - Main dashboard
 * - GameController - Games CRUD
 * - MaterialController - Materials CRUD
 * - BoxController - Boxes CRUD
 * - CategoryController - Categories CRUD
 * - TagController - Tags CRUD
 * - GroupController - Groups CRUD
 * - CalendarController - Calendar events
 * - SearchController - Global search
 * - ChangelogController - Audit log
 * - SettingsController - User settings
 * - ApiController - AJAX endpoints
 * - InstallController - Installation wizard
 *
 * AI NOTES:
 * - Translation function __($key) is defined in helpers/functions.php
 * - Flash messages persist for ONE request only (session-based)
 * - CSRF tokens are regenerated per-session, not per-request
 * - JSON responses set charset=utf-8 for German character support
 * - Redirects use Router::redirect() which validates URLs
 *
 * @package KindergartenOrganizer\Core
 * @since 1.0.0
 * =====================================================================================
 */

abstract class Controller
{
    protected array $data = [];
    protected string $layout = 'main';

    /**
     * Render a view
     */
    protected function render(string $view, array $data = []): void
    {
        // Merge data
        $this->data = array_merge($this->data, $data);

        // Extract data for view
        extract($this->data);

        // Load translations
        $lang = $this->loadTranslations();

        // Get flash messages
        $flash = Session::getFlash();

        // Get validation errors
        $errors = Session::getErrors();

        // Generate CSRF token
        $csrfToken = Session::csrfToken();

        // Start output buffering for content
        ob_start();

        // Include the view
        $viewFile = SRC_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$view}");
        }
        include $viewFile;

        // Get content
        $content = ob_get_clean();

        // Include the layout
        $layoutFile = SRC_PATH . '/views/layouts/' . $this->layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Render a view without layout
     */
    protected function renderPartial(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);

        $lang = $this->loadTranslations();

        $viewFile = SRC_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$view}");
        }
        include $viewFile;
    }

    /**
     * Set the layout
     */
    protected function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Load language translations
     */
    protected function loadTranslations(): array
    {
        static $langs = [];

        $language = userPreference('language', 'de');

        if (!isset($langs[$language])) {
            $langFile = SRC_PATH . '/lang/' . $language . '.php';
            if (file_exists($langFile)) {
                $langs[$language] = require $langFile;
            } else {
                // Fallback to German
                $langFile = SRC_PATH . '/lang/de.php';
                $langs[$language] = file_exists($langFile) ? require $langFile : [];
            }
        }

        return $langs[$language];
    }

    /**
     * Get translation
     */
    protected function trans(string $key, array $replace = []): string
    {
        $lang = $this->loadTranslations();
        $text = $lang[$key] ?? $key;

        foreach ($replace as $search => $value) {
            $text = str_replace(':' . $search, (string)$value, $text);
        }

        return $text;
    }

    /**
     * Send JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void
    {
        Router::redirect($url);
    }

    /**
     * Redirect back with data
     */
    protected function back(): void
    {
        Router::back();
    }

    /**
     * Set flash message and redirect
     */
    protected function redirectWithMessage(string $url, string $type, string $message): void
    {
        Session::setFlash($type, $message);
        $this->redirect($url);
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Session::setFlash('error', __('auth.please_login'));
            $this->redirect('/login');
        }
    }

    /**
     * Get POST data
     */
    protected function getPost(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function getQuery(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Get request method
     */
    protected function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Verify CSRF token
     */
    protected function verifyCsrf(): bool
    {
        $token = $this->getPost('csrf_token') ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return Session::verifyCsrf($token);
    }

    /**
     * Require CSRF token verification
     */
    protected function requireCsrf(): void
    {
        if (!$this->verifyCsrf()) {
            if ($this->isAjax()) {
                $this->json(['error' => __('auth.invalid_csrf')], 403);
            } else {
                Session::setFlash('error', __('auth.invalid_request'));
                $this->back();
            }
        }
    }

    /**
     * Get uploaded file
     */
    protected function getFile(string $key): ?array
    {
        if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return $_FILES[$key];
    }

    /**
     * Validate and sanitize an image_path value from user input.
     *
     * AI NOTE: image_path comes from a hidden form field populated by the JS image
     * uploader. It should match the format: "type/full/filename.webp"
     * (e.g., "games/full/20260115_abc123.webp"). If the path doesn't match this
     * format, it could be used for path traversal attacks when passed to unlink().
     *
     * SECURITY: Prevents arbitrary file deletion via crafted image_path values
     * like "../../config/database.php" stored in the DB and later passed to unlink().
     *
     * @param string $path The raw image_path from POST input
     * @return string Sanitized path, or empty string if invalid
     */
    protected function sanitizeImagePath(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // Must match: word_chars/full/word_chars.webp (e.g., "games/full/20260115_abc123.webp")
        // Note: dots are NOT allowed in the filename portion to prevent double-extension attacks
        // (e.g., "image.php.webp" would be rejected)
        if (!preg_match('#^[a-zA-Z0-9_-]+/(full)/[a-zA-Z0-9_-]+\.webp$#', $path)) {
            Logger::security('Invalid image_path rejected', ['path' => $path]);
            return '';
        }

        return $path;
    }

    /**
     * Set page title
     */
    protected function setTitle(string $title): void
    {
        $this->data['pageTitle'] = $title;
    }

    /**
     * Add breadcrumb
     */
    protected function addBreadcrumb(string $label, ?string $url = null): void
    {
        if (!isset($this->data['breadcrumbs'])) {
            $this->data['breadcrumbs'] = [];
        }
        $this->data['breadcrumbs'][] = ['label' => $label, 'url' => $url];
    }
}
