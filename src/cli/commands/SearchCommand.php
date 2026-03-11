<?php
declare(strict_types=1);

class SearchCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function search(array $parsed): void
    {
        $query = $parsed['args'][0] ?? '';
        $type = (string) ($parsed['options']['type'] ?? 'all');

        if (empty($query)) {
            $this->fmt->error('Please provide a search query.');
            return;
        }

        $result = (new SearchService())->search($query, $type);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $results = $result->data['results'];
        $counts = $result->data['counts'] ?? [];
        $totalCount = 0;

        if (!empty($results['games'])) {
            $this->fmt->info('Games (' . count($results['games']) . '):');
            $rows = [];
            foreach ($results['games'] as $game) {
                $rows[] = ['ID' => $game['id'], 'Name' => $game['name']];
            }
            $this->fmt->table(['ID', 'Name'], $rows);
            $this->fmt->newline();
            $totalCount += count($results['games']);
        }

        if (!empty($results['materials'])) {
            $this->fmt->info('Materials (' . count($results['materials']) . '):');
            $rows = [];
            foreach ($results['materials'] as $mat) {
                $rows[] = ['ID' => $mat['id'], 'Name' => $mat['name']];
            }
            $this->fmt->table(['ID', 'Name'], $rows);
            $this->fmt->newline();
            $totalCount += count($results['materials']);
        }

        if (!empty($results['boxes'])) {
            $this->fmt->info('Boxes (' . count($results['boxes']) . '):');
            $rows = [];
            foreach ($results['boxes'] as $box) {
                $rows[] = ['ID' => $box['id'], 'Name' => $box['name']];
            }
            $this->fmt->table(['ID', 'Name'], $rows);
            $this->fmt->newline();
            $totalCount += count($results['boxes']);
        }

        if (!empty($results['tags'])) {
            $this->fmt->info('Tags (' . count($results['tags']) . '):');
            $rows = [];
            foreach ($results['tags'] as $tag) {
                $rows[] = ['ID' => $tag['id'], 'Name' => $tag['name']];
            }
            $this->fmt->table(['ID', 'Name'], $rows);
            $this->fmt->newline();
            $totalCount += count($results['tags']);
        }

        if (!empty($results['groups'])) {
            $this->fmt->info('Groups (' . count($results['groups']) . '):');
            $rows = [];
            foreach ($results['groups'] as $group) {
                $rows[] = ['ID' => $group['id'], 'Name' => $group['name']];
            }
            $this->fmt->table(['ID', 'Name'], $rows);
            $this->fmt->newline();
            $totalCount += count($results['groups']);
        }

        if ($totalCount === 0) {
            $this->fmt->warn('No results found for "' . $query . '".');
        }
    }
}
