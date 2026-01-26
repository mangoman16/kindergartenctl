<?php
/**
 * Installation Controller
 */

class InstallController extends Controller
{
    public function __construct()
    {
        $this->setLayout('install');
    }

    /**
     * Redirect to first step
     */
    public function index(): void
    {
        $this->redirect('/install/step1');
    }

    /**
     * Step 1: Requirements Check
     */
    public function step1(): void
    {
        $requirements = $this->checkRequirements();
        $allPassed = !in_array(false, array_column($requirements, 'passed'));

        $this->setTitle(__('install.requirements'));
        $this->render('install/step1', [
            'currentStep' => 1,
            'requirements' => $requirements,
            'allPassed' => $allPassed,
        ]);
    }

    /**
     * Step 2: Database Configuration
     */
    public function step2(): void
    {
        $this->setTitle(__('install.database'));
        $this->render('install/step2', [
            'currentStep' => 2,
        ]);
    }

    /**
     * Test database connection
     */
    public function testConnection(): void
    {
        $config = [
            'host' => $this->getPost('host', 'localhost'),
            'port' => $this->getPost('port', 3306),
            'database' => $this->getPost('database'),
            'username' => $this->getPost('username'),
            'password' => $this->getPost('password'),
        ];

        $result = Database::testConnectionWithDetails($config);

        // Always return JSON for this endpoint (used by AJAX)
        if ($this->isAjax()) {
            $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ]);
            return;
        }

        // Fallback for non-AJAX requests
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        $this->redirect('/install/step2');
    }

    /**
     * Save database configuration and create tables
     */
    public function saveDatabase(): void
    {
        $config = [
            'host' => $this->getPost('host', 'localhost'),
            'port' => (int)$this->getPost('port', 3306),
            'database' => $this->getPost('database'),
            'username' => $this->getPost('username'),
            'password' => $this->getPost('password'),
        ];

        // Validate
        $validator = Validator::make($config, [
            'host' => 'required',
            'database' => 'required',
            'username' => 'required',
        ]);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($config);
            $this->redirect('/install/step2');
            return;
        }

        // Test connection
        if (!Database::testConnection($config)) {
            Session::setFlash('error', __('install.db_connection_failed'));
            Session::setOldInput($config);
            $this->redirect('/install/step2');
            return;
        }

        // Create database if it doesn't exist
        if (!Database::createDatabase($config)) {
            Session::setFlash('error', 'Datenbank konnte nicht erstellt werden.');
            Session::setOldInput($config);
            $this->redirect('/install/step2');
            return;
        }

        // Save configuration
        if (!Database::saveConfig($config)) {
            Session::setFlash('error', 'Konfiguration konnte nicht gespeichert werden.');
            Session::setOldInput($config);
            $this->redirect('/install/step2');
            return;
        }

        // Reload config and run schema
        Database::setConfig(array_merge($config, [
            'driver' => 'mysql',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ]));

        if (!Database::runSchema()) {
            Session::setFlash('error', 'Tabellen konnten nicht erstellt werden.');
            $this->redirect('/install/step2');
            return;
        }

        Session::setFlash('success', __('install.db_created'));
        $this->redirect('/install/step3');
    }

    /**
     * Step 3: Create Admin User
     */
    public function step3(): void
    {
        $this->setTitle(__('install.admin_user'));
        $this->render('install/step3', [
            'currentStep' => 3,
        ]);
    }

    /**
     * Create admin user
     */
    public function createAdmin(): void
    {
        $data = [
            'username' => $this->getPost('username'),
            'email' => $this->getPost('email'),
            'password' => $this->getPost('password'),
            'password_confirmation' => $this->getPost('password_confirmation'),
        ];

        // Validate with password complexity requirements
        $validator = Validator::make($data, [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email',
            'password' => 'required|password|confirmed',
        ]);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput($data);
            $this->redirect('/install/step3');
            return;
        }

        // Load database config
        Database::loadConfig();

        // Create user
        require_once SRC_PATH . '/models/User.php';

        $userId = User::createUser(
            $data['username'],
            $data['email'],
            $data['password']
        );

        if (!$userId) {
            Session::setFlash('error', 'Administrator konnte nicht erstellt werden.');
            Session::setOldInput($data);
            $this->redirect('/install/step3');
            return;
        }

        Session::setFlash('success', __('install.admin_created'));
        $this->redirect('/install/step4');
    }

    /**
     * Step 4: Email Configuration (Optional)
     */
    public function step4(): void
    {
        $this->setTitle(__('install.email_setup'));
        $this->render('install/step4', [
            'currentStep' => 4,
        ]);
    }

    /**
     * Save email configuration
     */
    public function saveEmail(): void
    {
        $data = [
            'smtp_host' => $this->getPost('smtp_host'),
            'smtp_port' => $this->getPost('smtp_port', 587),
            'smtp_username' => $this->getPost('smtp_username'),
            'smtp_password' => $this->getPost('smtp_password'),
            'smtp_encryption' => $this->getPost('smtp_encryption', 'tls'),
            'smtp_from_email' => $this->getPost('smtp_from_email'),
            'smtp_from_name' => $this->getPost('smtp_from_name', 'Kindergarten Spiele Organizer'),
        ];

        // Load database config
        Database::loadConfig();
        $db = Database::getInstance();

        // Save settings to database
        foreach ($data as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO settings (setting_key, setting_value)
                VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute(['key' => $key, 'value' => $value]);
        }

        $this->finishInstallation();
    }

    /**
     * Skip email configuration
     */
    public function skipEmail(): void
    {
        $this->finishInstallation();
    }

    /**
     * Finish installation
     */
    private function finishInstallation(): void
    {
        // Create installed.lock file
        file_put_contents(ROOT_PATH . '/installed.lock', date('Y-m-d H:i:s'));

        Session::setFlash('success', __('install.complete_title'));
        $this->redirect('/install/complete');
    }

    /**
     * Step 5: Complete
     */
    public function complete(): void
    {
        $this->setTitle(__('install.complete'));
        $this->render('install/complete', [
            'currentStep' => 5,
        ]);
    }

    /**
     * Check system requirements and create directories if needed
     */
    private function checkRequirements(): array
    {
        $requirements = [];

        // PHP Version
        $requirements[] = [
            'name' => 'PHP Version >= 8.0',
            'passed' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'current' => PHP_VERSION,
        ];

        // PDO Extension
        $requirements[] = [
            'name' => 'PDO Extension',
            'passed' => extension_loaded('pdo'),
            'current' => extension_loaded('pdo') ? 'Installiert' : 'Fehlt',
        ];

        // PDO MySQL Extension
        $requirements[] = [
            'name' => 'PDO MySQL Extension',
            'passed' => extension_loaded('pdo_mysql'),
            'current' => extension_loaded('pdo_mysql') ? 'Installiert' : 'Fehlt',
        ];

        // GD Extension
        $requirements[] = [
            'name' => 'GD Extension (Bilder)',
            'passed' => extension_loaded('gd'),
            'current' => extension_loaded('gd') ? 'Installiert' : 'Fehlt',
        ];

        // mbstring Extension
        $requirements[] = [
            'name' => 'mbstring Extension',
            'passed' => extension_loaded('mbstring'),
            'current' => extension_loaded('mbstring') ? 'Installiert' : 'Fehlt',
        ];

        // JSON Extension
        $requirements[] = [
            'name' => 'JSON Extension',
            'passed' => extension_loaded('json'),
            'current' => extension_loaded('json') ? 'Installiert' : 'Fehlt',
        ];

        // Directory permissions - create if not exists
        $directories = [
            'uploads' => PUBLIC_PATH . '/uploads',
            'uploads/games/full' => PUBLIC_PATH . '/uploads/games/full',
            'uploads/games/thumbs' => PUBLIC_PATH . '/uploads/games/thumbs',
            'uploads/boxes/full' => PUBLIC_PATH . '/uploads/boxes/full',
            'uploads/boxes/thumbs' => PUBLIC_PATH . '/uploads/boxes/thumbs',
            'uploads/categories/full' => PUBLIC_PATH . '/uploads/categories/full',
            'uploads/categories/thumbs' => PUBLIC_PATH . '/uploads/categories/thumbs',
            'uploads/tags/full' => PUBLIC_PATH . '/uploads/tags/full',
            'uploads/tags/thumbs' => PUBLIC_PATH . '/uploads/tags/thumbs',
            'uploads/materials/full' => PUBLIC_PATH . '/uploads/materials/full',
            'uploads/materials/thumbs' => PUBLIC_PATH . '/uploads/materials/thumbs',
            'temp' => TEMP_PATH,
            'storage/logs' => STORAGE_PATH . '/logs',
            'storage/cache' => STORAGE_PATH . '/cache',
            'src/config' => SRC_PATH . '/config',
        ];

        foreach ($directories as $name => $path) {
            // Try to create directory if it doesn't exist
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }

            $writable = is_dir($path) && is_writable($path);

            // Only show main directories in requirements (not all subdirs)
            if (!str_contains($name, '/') || $name === 'storage/logs' || $name === 'storage/cache' || $name === 'src/config') {
                $requirements[] = [
                    'name' => "Verzeichnis '{$name}'",
                    'passed' => $writable,
                    'current' => $writable ? 'Beschreibbar' : 'Nicht beschreibbar',
                ];
            }
        }

        return $requirements;
    }
}
