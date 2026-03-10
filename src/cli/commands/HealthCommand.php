<?php
declare(strict_types=1);

class HealthCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function check(array $parsed): void
    {
        $this->fmt->line($this->fmt->bold('Health Check'));
        $this->fmt->newline();

        $allOk = true;

        // PHP version
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.0.0', '>=');
        $this->printCheck('PHP Version', $phpVersion, $phpOk);
        $allOk = $allOk && $phpOk;

        // Required extensions
        $extensions = ['pdo', 'pdo_sqlite', 'mbstring', 'json'];
        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            $this->printCheck('Extension: ' . $ext, $loaded ? 'loaded' : 'MISSING', $loaded);
            $allOk = $allOk && $loaded;
        }

        // Database
        try {
            $db = Database::getInstance();
            $db->query("SELECT 1");
            $this->printCheck('Database', 'connected', true);
        } catch (Exception $e) {
            $this->printCheck('Database', 'FAILED: ' . $e->getMessage(), false);
            $allOk = false;
        }

        // Storage directory
        $storageWritable = is_dir(STORAGE_PATH) && is_writable(STORAGE_PATH);
        $this->printCheck('Storage directory', $storageWritable ? 'writable' : 'NOT WRITABLE', $storageWritable);
        $allOk = $allOk && $storageWritable;

        // Uploads directory
        $uploadsWritable = is_dir(UPLOADS_PATH) && is_writable(UPLOADS_PATH);
        $this->printCheck('Uploads directory', $uploadsWritable ? 'writable' : 'NOT WRITABLE', $uploadsWritable);
        $allOk = $allOk && $uploadsWritable;

        // Installed flag
        $installed = AppBoot::isInstalled();
        $this->printCheck('Installation', $installed ? 'installed' : 'NOT INSTALLED', $installed);
        $allOk = $allOk && $installed;

        $this->fmt->newline();
        if ($allOk) {
            $this->fmt->success('All checks passed.');
        } else {
            $this->fmt->error('Some checks failed.');
        }
    }

    public function help(array $parsed): void
    {
        // Delegate back to CliApp help (shouldn't normally reach here)
        $this->fmt->info('Run "php bin/kindergartenctl help" for available commands.');
    }

    private function printCheck(string $label, string $value, bool $ok): void
    {
        $status = $ok ? $this->fmt->green(' OK ') : $this->fmt->red(' FAIL ');
        $this->fmt->line('  ' . $status . ' ' . $label . ': ' . $value);
    }
}
