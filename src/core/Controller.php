<?php
/**
 * Base Controller Class
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
        static $lang = null;

        if ($lang === null) {
            $langFile = SRC_PATH . '/lang/de.php';
            if (file_exists($langFile)) {
                $lang = require $langFile;
            } else {
                $lang = [];
            }
        }

        return $lang;
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
            Session::setFlash('error', 'Bitte melden Sie sich an.');
            $this->redirect('/login');
        }
    }

    /**
     * Get POST data
     */
    protected function getPost(string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function getQuery(string $key = null, $default = null)
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
                $this->json(['error' => 'Ungültiges CSRF-Token'], 403);
            } else {
                Session::setFlash('error', 'Ungültige Anfrage. Bitte versuchen Sie es erneut.');
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
