<?php
declare(strict_types=1);

class DbCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function migrate(array $parsed): void
    {
        $this->fmt->info('Running database migrations...');

        try {
            $db = Database::getInstance();
            $db->runMigrations();
            $this->fmt->success('Migrations completed successfully.');
        } catch (Exception $e) {
            $this->fmt->error('Migration failed: ' . $e->getMessage());
        }
    }
}
