<?php
declare(strict_types=1);

class CategoryCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function list(array $parsed): void
    {
        $result = (new CategoryService())->list();

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $rows = [];
        foreach ($result->data['categories'] as $cat) {
            $rows[] = [
                'ID'    => $cat['id'],
                'Name'  => $cat['name'],
                'Order' => $cat['sort_order'] ?? '-',
                'Games' => $cat['game_count'] ?? 0,
            ];
        }

        $this->fmt->table(['ID', 'Name', 'Order', 'Games'], $rows);
    }

    public function show(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid category ID.');
            return;
        }

        $result = (new CategoryService())->get($id);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $cat = $result->data['category'];

        $this->fmt->detail([
            'id'          => $cat['id'] ?? '',
            'name'        => $cat['name'] ?? '',
            'description' => $cat['description'] ?? '',
            'sort_order'  => $cat['sort_order'] ?? '',
            'game_count'  => $cat['game_count'] ?? 0,
            'created_at'  => $cat['created_at'] ?? '',
        ], [
            'id'          => 'ID',
            'name'        => 'Name',
            'description' => 'Description',
            'sort_order'  => 'Sort Order',
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
        if (isset($options['sort-order'])) $data['sort_order'] = (int) $options['sort-order'];

        $result = (new CategoryService())->create($data);
        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New category ID: ' . $result->data['id']);
        }
    }

    public function update(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid category ID.');
            return;
        }

        $options = $parsed['options'];
        $data = [];
        if (isset($options['name'])) $data['name'] = (string) $options['name'];
        if (isset($options['description'])) $data['description'] = (string) $options['description'];
        if (isset($options['sort-order'])) $data['sort_order'] = (int) $options['sort-order'];

        if (empty($data)) {
            $this->fmt->warn('No fields to update. Use --name, --description, --sort-order.');
            return;
        }

        $result = (new CategoryService())->update($id, $data);
        $this->fmt->result($result);
    }

    public function delete(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid category ID.');
            return;
        }

        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to delete category #' . $id . '?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $result = (new CategoryService())->delete($id);
        $this->fmt->result($result);
    }
}
