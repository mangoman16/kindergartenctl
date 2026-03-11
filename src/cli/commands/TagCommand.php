<?php
declare(strict_types=1);

class TagCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function list(array $parsed): void
    {
        $result = (new TagService())->list();

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $rows = [];
        foreach ($result->data['tags'] as $tag) {
            $rows[] = [
                'ID'    => $tag['id'],
                'Name'  => $tag['name'],
                'Color' => $tag['color'] ?? '-',
                'Games' => $tag['game_count'] ?? 0,
            ];
        }

        $this->fmt->table(['ID', 'Name', 'Color', 'Games'], $rows);
    }

    public function show(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid tag ID.');
            return;
        }

        $result = (new TagService())->get($id);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $tag = $result->data['tag'];

        $this->fmt->detail([
            'id'          => $tag['id'] ?? '',
            'name'        => $tag['name'] ?? '',
            'description' => $tag['description'] ?? '',
            'color'       => $tag['color'] ?? '-',
            'game_count'  => $tag['game_count'] ?? 0,
            'created_at'  => $tag['created_at'] ?? '',
        ], [
            'id'          => 'ID',
            'name'        => 'Name',
            'description' => 'Description',
            'color'       => 'Color',
            'game_count'  => 'Games',
            'created_at'  => 'Created',
        ]);
    }

    public function create(array $parsed): void
    {
        $options = $parsed['options'];

        if (empty($options['name'])) {
            $this->fmt->error('The --name option is required.');
            return;
        }

        $data = ['name' => (string) $options['name']];
        if (isset($options['description'])) $data['description'] = (string) $options['description'];
        if (isset($options['color'])) $data['color'] = (string) $options['color'];

        $result = (new TagService())->create($data);
        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New tag ID: ' . $result->data['id']);
        }
    }

    public function update(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid tag ID.');
            return;
        }

        $options = $parsed['options'];
        $data = [];
        if (isset($options['name'])) $data['name'] = (string) $options['name'];
        if (isset($options['description'])) $data['description'] = (string) $options['description'];
        if (isset($options['color'])) $data['color'] = (string) $options['color'];

        if (empty($data)) {
            $this->fmt->warn('No fields to update. Use --name, --description, --color.');
            return;
        }

        $result = (new TagService())->update($id, $data);
        $this->fmt->result($result);
    }

    public function delete(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid tag ID.');
            return;
        }

        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to delete tag #' . $id . '?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $result = (new TagService())->delete($id);
        $this->fmt->result($result);
    }
}
