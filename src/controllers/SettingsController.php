<?php
/**
 * Settings Controller
 */

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * Show settings page
     */
    public function index(): void
    {
        require_once SRC_PATH . '/models/User.php';

        $user = User::find(Auth::id());
        if (!$user) {
            Session::setFlash('error', 'Benutzer nicht gefunden.');
            $this->redirect('/login');
            return;
        }

        // Get IP bans
        $db = Database::getInstance();
        $bans = $db->query("SELECT * FROM ip_bans ORDER BY created_at DESC")->fetchAll();

        // Get storage info
        $uploadsSize = $this->getDirectorySize(UPLOADS_PATH);
        $tempSize = $this->getDirectorySize(TEMP_PATH);

        // Get SMTP settings
        $smtpConfig = $this->getSmtpConfig();

        // Get user preferences
        $preferences = $this->getUserPreferences();

        $this->setTitle(__('settings.title'));
        $this->addBreadcrumb(__('settings.title'));

        $this->render('settings/index', [
            'user' => $user,
            'bans' => $bans,
            'uploadsSize' => $this->formatBytes($uploadsSize),
            'tempSize' => $this->formatBytes($tempSize),
            'smtp' => [
                'host' => $smtpConfig['smtp_host'] ?? '',
                'port' => $smtpConfig['smtp_port'] ?? 587,
                'username' => $smtpConfig['smtp_user'] ?? '',
                'password' => $smtpConfig['smtp_pass'] ?? '',
                'from_email' => $smtpConfig['smtp_from'] ?? '',
                'from_name' => $smtpConfig['smtp_from_name'] ?? '',
                'encryption' => $smtpConfig['smtp_encryption'] ?? 'tls',
            ],
            'preferences' => $preferences,
        ]);
    }

    /**
     * Get SMTP configuration
     */
    private function getSmtpConfig(): array
    {
        $configPath = STORAGE_PATH . '/smtp.php';
        if (file_exists($configPath)) {
            return include $configPath;
        }

        return [
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_user' => '',
            'smtp_pass' => '',
            'smtp_from' => '',
            'smtp_from_name' => 'Kindergarten Spiele Organizer',
            'smtp_encryption' => 'tls',
        ];
    }

    /**
     * Get user preferences
     */
    private function getUserPreferences(): array
    {
        $configPath = STORAGE_PATH . '/preferences.php';
        if (file_exists($configPath)) {
            return include $configPath;
        }

        return [
            'items_per_page' => 24,
            'default_view' => 'grid',
        ];
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(): void
    {
        $this->requireCsrf();

        $itemsPerPage = (int)($_POST['items_per_page'] ?? 24);
        $defaultView = $_POST['default_view'] ?? 'grid';

        // Validate items per page (12, 24, 48, 96)
        $allowedPerPage = [12, 24, 48, 96];
        if (!in_array($itemsPerPage, $allowedPerPage)) {
            $itemsPerPage = 24;
        }

        // Validate default view
        $allowedViews = ['grid', 'list'];
        if (!in_array($defaultView, $allowedViews)) {
            $defaultView = 'grid';
        }

        // Save preferences
        $config = [
            'items_per_page' => $itemsPerPage,
            'default_view' => $defaultView,
        ];

        $configPath = STORAGE_PATH . '/preferences.php';
        $content = "<?php\nreturn " . var_export($config, true) . ";\n";

        if (file_put_contents($configPath, $content) === false) {
            Logger::error('Failed to save preferences', ['path' => $configPath]);
            Session::setFlash('error', 'Einstellungen konnten nicht gespeichert werden.');
            $this->redirect('/settings');
            return;
        }

        Session::setFlash('success', 'Einstellungen wurden gespeichert.');
        $this->redirect('/settings');
    }

    /**
     * Update password
     */
    public function updatePassword(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/User.php';

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $user = User::find(Auth::id());
        if (!$user) {
            Session::setFlash('error', 'Benutzer nicht gefunden.');
            $this->redirect('/login');
            return;
        }

        // Validate current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            Session::setFlash('error', 'Das aktuelle Passwort ist falsch.');
            $this->redirect('/user/settings');
            return;
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            Session::setFlash('error', __('validation.password_min_length'));
            $this->redirect('/user/settings');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Session::setFlash('error', __('validation.passwords_dont_match'));
            $this->redirect('/user/settings');
            return;
        }

        // Update password
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            'hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => Auth::id(),
        ]);

        Session::setFlash('success', __('settings.password_changed'));
        $this->redirect('/user/settings');
    }

    /**
     * Update email
     */
    public function updateEmail(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/User.php';

        $newEmail = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = User::find(Auth::id());
        if (!$user) {
            Session::setFlash('error', 'Benutzer nicht gefunden.');
            $this->redirect('/login');
            return;
        }

        // Validate password
        if (!password_verify($password, $user['password_hash'])) {
            Session::setFlash('error', 'Das Passwort ist falsch.');
            $this->redirect('/user/settings');
            return;
        }

        // Validate email
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', __('validation.invalid_email'));
            $this->redirect('/user/settings');
            return;
        }

        // Update email
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET email = :email, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            'email' => $newEmail,
            'id' => Auth::id(),
        ]);

        Session::setFlash('success', __('settings.email_changed'));
        $this->redirect('/user/settings');
    }

    /**
     * Update SMTP settings
     */
    public function updateSmtp(): void
    {
        $this->requireCsrf();

        $smtpHost = trim($_POST['smtp_host'] ?? '');
        $smtpPort = (int)($_POST['smtp_port'] ?? 587);
        $smtpUser = trim($_POST['smtp_username'] ?? '');
        $smtpPass = $_POST['smtp_password'] ?? '';
        $smtpFrom = trim($_POST['smtp_from_email'] ?? '');
        $smtpFromName = trim($_POST['smtp_from_name'] ?? 'KindergartenOrganizer');
        $smtpEncryption = $_POST['smtp_encryption'] ?? 'tls';

        // Validate SMTP port (1-65535)
        if ($smtpPort < 1 || $smtpPort > 65535) {
            $smtpPort = 587;
        }

        // Validate encryption type
        $allowedEncryption = ['tls', 'ssl', 'none', ''];
        if (!in_array($smtpEncryption, $allowedEncryption, true)) {
            $smtpEncryption = 'tls';
        }

        // Validate email format for from address if provided
        if (!empty($smtpFrom) && !filter_var($smtpFrom, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Ungültige Absender-E-Mail-Adresse.');
            $this->redirect('/settings/email');
            return;
        }

        // Load existing config to preserve password if not changed
        $existingConfig = $this->getSmtpConfig();
        if (empty($smtpPass) && !empty($existingConfig['smtp_pass'])) {
            // Keep existing (already encrypted) password
            $encryptedPass = $existingConfig['smtp_pass'];
        } else {
            // Encrypt new password before storage
            $encryptedPass = encryptValue($smtpPass);
        }

        // Save to storage file (password stored encrypted)
        $config = [
            'smtp_host' => $smtpHost,
            'smtp_port' => $smtpPort,
            'smtp_user' => $smtpUser,
            'smtp_pass' => $encryptedPass,
            'smtp_from' => $smtpFrom,
            'smtp_from_name' => $smtpFromName,
            'smtp_encryption' => $smtpEncryption,
        ];

        $configPath = STORAGE_PATH . '/smtp.php';
        $content = "<?php\nreturn " . var_export($config, true) . ";\n";

        if (file_put_contents($configPath, $content, LOCK_EX) === false) {
            Logger::error('Failed to save SMTP config', ['path' => $configPath]);
            Session::setFlash('error', 'E-Mail-Einstellungen konnten nicht gespeichert werden.');
            $this->redirect('/settings/email');
            return;
        }

        // Restrict file permissions
        chmod($configPath, 0640);

        Session::setFlash('success', 'E-Mail-Einstellungen wurden gespeichert.');
        $this->redirect('/settings/email');
    }

    /**
     * Test SMTP settings
     */
    public function testSmtp(): void
    {
        $this->requireCsrf();

        $testEmail = trim($_POST['test_email'] ?? '');

        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Bitte geben Sie eine gültige E-Mail-Adresse ein.');
            $this->redirect('/settings/email');
            return;
        }

        // Try to send test email
        require_once SRC_PATH . '/services/Mailer.php';
        $mailer = new Mailer();

        if (!$mailer->isConfigured()) {
            Session::setFlash('error', 'SMTP ist nicht konfiguriert. Bitte speichern Sie zuerst die Einstellungen.');
            $this->redirect('/settings/email');
            return;
        }

        if ($mailer->sendTestEmail($testEmail)) {
            Session::setFlash('success', __('settings.smtp_test_success'));
        } else {
            $errors = $mailer->getErrors();
            $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unbekannter Fehler';
            Session::setFlash('error', str_replace(':error', $errorMsg, __('settings.smtp_test_failed')));
        }

        $this->redirect('/settings/email');
    }

    /**
     * Ban an IP address
     */
    public function banIp(): void
    {
        $this->requireCsrf();

        $ip = trim($_POST['ip'] ?? '');
        $reason = trim($_POST['reason'] ?? 'Manuell gesperrt');

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            Session::setFlash('error', 'Ungültige IP-Adresse.');
            $this->redirect('/settings/data');
            return;
        }

        $db = Database::getInstance();

        // Check if already banned
        $stmt = $db->prepare("SELECT id FROM ip_bans WHERE ip_address = :ip");
        $stmt->execute(['ip' => $ip]);
        if ($stmt->fetch()) {
            Session::setFlash('error', 'Diese IP-Adresse ist bereits gesperrt.');
            $this->redirect('/settings/data');
            return;
        }

        // Insert ban (is_permanent = 1 to ensure manual bans are always active)
        $stmt = $db->prepare("INSERT INTO ip_bans (ip_address, reason, is_permanent, created_at) VALUES (:ip, :reason, 1, NOW())");
        $stmt->execute(['ip' => $ip, 'reason' => $reason]);

        Session::setFlash('success', 'IP-Adresse wurde gesperrt.');
        $this->redirect('/settings/data');
    }

    /**
     * Unban an IP address
     */
    public function unbanIp(): void
    {
        $this->requireCsrf();

        $ip = trim($_POST['ip'] ?? '');

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            Session::setFlash('error', 'Ungültige IP-Adresse.');
            $this->redirect('/settings/data');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM ip_bans WHERE ip_address = :ip");
        $stmt->execute(['ip' => $ip]);

        Session::setFlash('success', 'IP-Sperre wurde aufgehoben.');
        $this->redirect('/settings/data');
    }

    /**
     * Show customization page
     */
    public function showCustomization(): void
    {
        $this->setTitle(__('settings.customization'));
        $this->addBreadcrumb(__('settings.title'), '/settings');
        $this->addBreadcrumb(__('settings.customization'));

        $this->render('settings/customization');
    }

    /**
     * Show language page
     */
    public function showLanguage(): void
    {
        $this->setTitle(__('settings.language'));
        $this->addBreadcrumb(__('settings.title'), '/settings');
        $this->addBreadcrumb(__('settings.language'));

        $this->render('settings/language');
    }

    /**
     * Show email settings page
     */
    public function showEmail(): void
    {
        $smtpConfig = $this->getSmtpConfig();

        $this->setTitle(__('settings.email'));
        $this->addBreadcrumb(__('settings.title'), '/settings');
        $this->addBreadcrumb(__('settings.email'));

        $this->render('settings/email', [
            'smtp' => [
                'host' => $smtpConfig['smtp_host'] ?? '',
                'port' => $smtpConfig['smtp_port'] ?? 587,
                'username' => $smtpConfig['smtp_user'] ?? '',
                'password' => $smtpConfig['smtp_pass'] ?? '',
                'from_email' => $smtpConfig['smtp_from'] ?? '',
                'from_name' => $smtpConfig['smtp_from_name'] ?? '',
                'encryption' => $smtpConfig['smtp_encryption'] ?? 'tls',
            ],
        ]);
    }

    /**
     * Show debug page
     */
    public function showDebug(): void
    {
        $this->setTitle(__('settings.debug'));
        $this->addBreadcrumb(__('settings.title'), '/settings');
        $this->addBreadcrumb(__('settings.debug'));

        $this->render('settings/debug');
    }

    /**
     * Show data management page
     */
    public function showData(): void
    {
        $db = Database::getInstance();
        $bans = $db->query("SELECT * FROM ip_bans ORDER BY created_at DESC")->fetchAll();

        $uploadsSize = $this->getDirectorySize(UPLOADS_PATH);
        $tempSize = $this->getDirectorySize(TEMP_PATH);

        $this->setTitle(__('settings.data'));
        $this->addBreadcrumb(__('settings.title'), '/settings');
        $this->addBreadcrumb(__('settings.data'));

        $this->render('settings/data', [
            'bans' => $bans,
            'uploadsSize' => $this->formatBytes($uploadsSize),
            'tempSize' => $this->formatBytes($tempSize),
        ]);
    }

    /**
     * Show help wizard
     */
    public function help(): void
    {
        $this->setTitle(__('help.title'));
        $this->addBreadcrumb(__('settings.title'), '/settings');
        $this->addBreadcrumb(__('help.title'));

        $this->render('settings/help');
    }

    /**
     * Show user settings / profile page
     */
    public function userSettings(): void
    {
        require_once SRC_PATH . '/models/User.php';

        $user = User::find(Auth::id());
        if (!$user) {
            Session::setFlash('error', 'Benutzer nicht gefunden.');
            $this->redirect('/login');
            return;
        }

        $preferences = $this->getUserPreferences();

        // Load all users for user management
        $db = Database::getInstance();
        $users = $db->query("SELECT id, username, email, created_at FROM users ORDER BY id ASC")->fetchAll();

        $this->setTitle(__('user.settings'));
        $this->addBreadcrumb(__('user.settings'));

        $this->render('settings/user', [
            'user' => $user,
            'preferences' => $preferences,
            'users' => $users,
        ]);
    }

    /**
     * Update language from user settings page
     */
    public function updateUserLanguage(): void
    {
        $this->requireCsrf();

        $language = $_POST['language'] ?? 'de';

        $allowedLanguages = ['de', 'en'];
        if (!in_array($language, $allowedLanguages, true)) {
            $language = 'de';
        }

        $preferences = $this->getUserPreferences();
        $preferences['language'] = $language;
        $this->savePreferences($preferences);

        Session::setFlash('success', __('settings.language_changed'));
        $this->redirect('/user/settings');
    }

    /**
     * Create a new user
     */
    public function createUser(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/User.php';

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validate
        if (empty($username) || strlen($username) < 3) {
            Session::setFlash('error', __('validation.min_length', ['min' => 3]));
            $this->redirect('/user/settings');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', __('validation.invalid_email'));
            $this->redirect('/user/settings');
            return;
        }

        if (strlen($password) < 8) {
            Session::setFlash('error', __('validation.password_min_length'));
            $this->redirect('/user/settings');
            return;
        }

        if ($password !== $passwordConfirm) {
            Session::setFlash('error', __('validation.passwords_dont_match'));
            $this->redirect('/user/settings');
            return;
        }

        if (User::usernameExists($username)) {
            Session::setFlash('error', __('validation.duplicate'));
            $this->redirect('/user/settings');
            return;
        }

        if (User::emailExists($email)) {
            Session::setFlash('error', __('validation.duplicate'));
            $this->redirect('/user/settings');
            return;
        }

        $userId = User::createUser($username, $email, $password);

        if ($userId) {
            Session::setFlash('success', __('flash.created', ['item' => $username]));
        } else {
            Session::setFlash('error', __('flash.error'));
        }

        $this->redirect('/user/settings');
    }

    /**
     * Delete a user
     */
    public function deleteUser(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/User.php';

        $userId = (int)($_POST['user_id'] ?? 0);

        // Cannot delete yourself
        if ($userId === Auth::id()) {
            Session::setFlash('error', __('user.cannot_delete_self'));
            $this->redirect('/user/settings');
            return;
        }

        if ($userId <= 0) {
            Session::setFlash('error', __('validation.invalid_value'));
            $this->redirect('/user/settings');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            Session::setFlash('error', __('validation.invalid_value'));
            $this->redirect('/user/settings');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);

        Session::setFlash('success', __('flash.deleted', ['item' => $user['username']]));
        $this->redirect('/user/settings');
    }

    /**
     * Update language preference
     */
    public function updateLanguage(): void
    {
        $this->requireCsrf();

        $language = $_POST['language'] ?? 'de';

        // Validate language (only allow known languages)
        $allowedLanguages = ['de', 'en'];
        if (!in_array($language, $allowedLanguages, true)) {
            $language = 'de';
        }

        // Load existing preferences and merge
        $preferences = $this->getUserPreferences();
        $preferences['language'] = $language;
        $this->savePreferences($preferences);

        Session::setFlash('success', 'Sprache wurde geändert.');
        $this->redirect('/settings/language');
    }

    /**
     * Update theme customization
     */
    public function updateCustomization(): void
    {
        $this->requireCsrf();

        $themeColor = $_POST['theme_color'] ?? '';
        $themePattern = $_POST['theme_pattern'] ?? '';

        // Validate color format (hex color)
        if (!empty($themeColor) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $themeColor)) {
            $themeColor = '';
        }

        // Validate pattern (only allow known patterns)
        $allowedPatterns = ['none', 'dots', 'stars', 'hearts', 'clouds'];
        if (!in_array($themePattern, $allowedPatterns, true)) {
            $themePattern = 'none';
        }

        // Load existing preferences and merge
        $preferences = $this->getUserPreferences();
        $preferences['theme_color'] = $themeColor;
        $preferences['theme_pattern'] = $themePattern;
        $this->savePreferences($preferences);

        Session::setFlash('success', 'Design wurde aktualisiert.');
        $this->redirect('/settings/customization');
    }

    /**
     * Toggle debug mode
     */
    public function toggleDebug(): void
    {
        $this->requireCsrf();

        $debugFlagPath = ROOT_PATH . '/storage/debug.flag';

        if (file_exists($debugFlagPath)) {
            // Disable debug mode
            unlink($debugFlagPath);
            Session::setFlash('success', 'Debug-Modus wurde deaktiviert.');
        } else {
            // Enable debug mode
            file_put_contents($debugFlagPath, date('Y-m-d H:i:s'));
            Session::setFlash('success', 'Debug-Modus wurde aktiviert.');
        }

        $this->redirect('/settings/debug');
    }

    /**
     * Save preferences to file
     */
    private function savePreferences(array $config): bool
    {
        $configPath = STORAGE_PATH . '/preferences.php';
        $content = "<?php\nreturn " . var_export($config, true) . ";\n";

        if (file_put_contents($configPath, $content, LOCK_EX) === false) {
            Logger::error('Failed to save preferences', ['path' => $configPath]);
            return false;
        }

        return true;
    }

    /**
     * Clear temp directory
     */
    public function clearTemp(): void
    {
        $this->requireCsrf();

        $this->deleteDirectory(TEMP_PATH, false);

        Session::setFlash('success', 'Temporäre Dateien wurden gelöscht.');
        $this->redirect('/settings/data');
    }

    /**
     * Get directory size recursively
     */
    private function getDirectorySize(string $path): int
    {
        $size = 0;

        if (!is_dir($path)) {
            return 0;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Delete directory contents
     */
    private function deleteDirectory(string $path, bool $removeSelf = true): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        if ($removeSelf) {
            rmdir($path);
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
