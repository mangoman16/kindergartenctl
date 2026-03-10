<?php
declare(strict_types=1);

class ChangelogCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function list(array $parsed): void
    {
        $options = $parsed['options'];
        $limit = (int) ($options['limit'] ?? 50);

        $changelog = ChangelogService::getInstance();
        $entries = $changelog->getRecent($limit);

        // Client-side type filter if specified
        $type = $options['type'] ?? null;
        if ($type !== null) {
            $entries = array_filter($entries, function ($entry) use ($type) {
                return ($entry['entity_type'] ?? '') === $type;
            });
            $entries = array_values($entries);
        }

        if (empty($entries)) {
            $this->fmt->warn('No changelog entries found.');
            return;
        }

        $rows = [];
        foreach ($entries as $entry) {
            $rows[] = [
                'ID'     => $entry['id'],
                'Action' => $entry['action'] ?? '',
                'Type'   => $entry['entity_type'] ?? '',
                'Name'   => $entry['entity_name'] ?? '',
                'User'   => $entry['user_name'] ?? '-',
                'Date'   => $entry['created_at'] ?? '',
            ];
        }

        $this->fmt->table(['ID', 'Action', 'Type', 'Name', 'User', 'Date'], $rows);
    }

    public function clear(array $parsed): void
    {
        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to clear old changelog entries?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $changelog = ChangelogService::getInstance();
        $deleted = $changelog->cleanup(0);

        $this->fmt->success('Cleared ' . $deleted . ' changelog entries.');
    }
}
