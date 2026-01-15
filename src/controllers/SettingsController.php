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
            'smtpConfig' => $smtpConfig,
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
        file_put_contents($configPath, $content);

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

        // Validate current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            Session::setFlash('error', 'Das aktuelle Passwort ist falsch.');
            $this->redirect('/settings');
            return;
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            Session::setFlash('error', 'Das neue Passwort muss mindestens 8 Zeichen haben.');
            $this->redirect('/settings');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Session::setFlash('error', 'Die Passwörter stimmen nicht überein.');
            $this->redirect('/settings');
            return;
        }

        // Update password
        User::update(Auth::id(), [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        Session::setFlash('success', 'Passwort wurde geändert.');
        $this->redirect('/settings');
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

        // Validate password
        if (!password_verify($password, $user['password_hash'])) {
            Session::setFlash('error', 'Das Passwort ist falsch.');
            $this->redirect('/settings');
            return;
        }

        // Validate email
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Bitte geben Sie eine gültige E-Mail-Adresse ein.');
            $this->redirect('/settings');
            return;
        }

        // Check if email already exists
        $existingUser = User::findByEmail($newEmail);
        if ($existingUser && $existingUser['id'] !== Auth::id()) {
            Session::setFlash('error', 'Diese E-Mail-Adresse wird bereits verwendet.');
            $this->redirect('/settings');
            return;
        }

        // Update email
        User::update(Auth::id(), ['email' => $newEmail]);

        Session::setFlash('success', 'E-Mail-Adresse wurde geändert.');
        $this->redirect('/settings');
    }

    /**
     * Update SMTP settings
     */
    public function updateSmtp(): void
    {
        $this->requireCsrf();

        $smtpHost = trim($_POST['smtp_host'] ?? '');
        $smtpPort = (int)($_POST['smtp_port'] ?? 587);
        $smtpUser = trim($_POST['smtp_user'] ?? '');
        $smtpPass = $_POST['smtp_pass'] ?? '';
        $smtpFrom = trim($_POST['smtp_from'] ?? '');
        $smtpFromName = trim($_POST['smtp_from_name'] ?? 'Kindergarten Spiele Organizer');
        $smtpEncryption = $_POST['smtp_encryption'] ?? 'tls';

        // Load existing config to preserve password if not changed
        $existingConfig = $this->getSmtpConfig();
        if (empty($smtpPass) && !empty($existingConfig['smtp_pass'])) {
            $smtpPass = $existingConfig['smtp_pass'];
        }

        // Save to storage file
        $config = [
            'smtp_host' => $smtpHost,
            'smtp_port' => $smtpPort,
            'smtp_user' => $smtpUser,
            'smtp_pass' => $smtpPass,
            'smtp_from' => $smtpFrom,
            'smtp_from_name' => $smtpFromName,
            'smtp_encryption' => $smtpEncryption,
        ];

        $configPath = STORAGE_PATH . '/smtp.php';
        $content = "<?php\nreturn " . var_export($config, true) . ";\n";
        file_put_contents($configPath, $content);

        Session::setFlash('success', 'E-Mail-Einstellungen wurden gespeichert.');
        $this->redirect('/settings');
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
            $this->redirect('/settings');
            return;
        }

        // Try to send test email
        require_once SRC_PATH . '/services/Mailer.php';
        $mailer = new Mailer();

        if (!$mailer->isConfigured()) {
            Session::setFlash('error', 'SMTP ist nicht konfiguriert. Bitte speichern Sie zuerst die Einstellungen.');
            $this->redirect('/settings');
            return;
        }

        if ($mailer->sendTestEmail($testEmail)) {
            Session::setFlash('success', __('settings.smtp_test_success'));
        } else {
            $errors = $mailer->getErrors();
            $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unbekannter Fehler';
            Session::setFlash('error', str_replace(':error', $errorMsg, __('settings.smtp_test_failed')));
        }

        $this->redirect('/settings');
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
            $this->redirect('/settings');
            return;
        }

        $db = Database::getInstance();

        // Check if already banned
        $stmt = $db->prepare("SELECT id FROM ip_bans WHERE ip_address = :ip");
        $stmt->execute(['ip' => $ip]);
        if ($stmt->fetch()) {
            Session::setFlash('error', 'Diese IP-Adresse ist bereits gesperrt.');
            $this->redirect('/settings');
            return;
        }

        // Insert ban
        $stmt = $db->prepare("INSERT INTO ip_bans (ip_address, reason, created_at) VALUES (:ip, :reason, NOW())");
        $stmt->execute(['ip' => $ip, 'reason' => $reason]);

        Session::setFlash('success', 'IP-Adresse wurde gesperrt.');
        $this->redirect('/settings');
    }

    /**
     * Unban an IP address
     */
    public function unbanIp(): void
    {
        $this->requireCsrf();

        $ip = trim($_POST['ip'] ?? '');

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM ip_bans WHERE ip_address = :ip");
        $stmt->execute(['ip' => $ip]);

        Session::setFlash('success', 'IP-Sperre wurde aufgehoben.');
        $this->redirect('/settings');
    }

    /**
     * Clear temp directory
     */
    public function clearTemp(): void
    {
        $this->requireCsrf();

        $this->deleteDirectory(TEMP_PATH, false);

        Session::setFlash('success', 'Temporäre Dateien wurden gelöscht.');
        $this->redirect('/settings');
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
