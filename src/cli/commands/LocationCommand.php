<?php
declare(strict_types=1);

class LocationCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function list(array $parsed): void
    {
        $options = $parsed['options'];
        $sort = (string) ($options['sort'] ?? 'name');
        $dir = strtoupper((string) ($options['dir'] ?? 'ASC'));

        $result = (new LocationService())->list($sort, $dir);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $rows = [];
        foreach ($result->data['locations'] as $loc) {
            $rows[] = [
                'ID'    => $loc['id'],
                'Name'  => $loc['name'],
                'Boxes' => $loc['box_count'] ?? 0,
            ];
        }

        $this->fmt->table(['ID', 'Name', 'Boxes'], $rows);
    }

    public function show(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid location ID.');
            return;
        }

        $result = (new LocationService())->get($id);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $loc = $result->data['location'];

        $this->fmt->detail([
            'id'          => $loc['id'] ?? '',
            'name'        => $loc['name'] ?? '',
            'description' => $loc['description'] ?? '',
            'box_count'   => $loc['box_count'] ?? 0,
            'created_at'  => $loc['created_at'] ?? '',
        ], [
            'id'          => 'ID',
            'name'        => 'Name',
            'description' => 'Description',
            'box_count'   => 'Boxes',
            'created_at'  => 'Created',
        ]);

        if (!empty($result->data['boxes'])) {
            $this->fmt->newline();
            $this->fmt->info('Boxes at this location:');
            $rows = [];
            foreach ($result->data['boxes'] as $box) {
                $rows[] = ['ID' => $box['id'], 'Name' => $box['name']];
            }
            $this->fmt->table(['ID', 'Name'], $rows);
        }
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

        $result = (new LocationService())->create($data);
        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New location ID: ' . $result->data['id']);
        }
    }

    public function update(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid location ID.');
            return;
        }

        $options = $parsed['options'];
        $data = [];
        if (isset($options['name'])) $data['name'] = (string) $options['name'];
        if (isset($options['description'])) $data['description'] = (string) $options['description'];

        if (empty($data)) {
            $this->fmt->warn('No fields to update. Use --name, --description.');
            return;
        }

        $result = (new LocationService())->update($id, $data);
        $this->fmt->result($result);
    }

    public function delete(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid location ID.');
            return;
        }

        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to delete location #' . $id . '?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $result = (new LocationService())->delete($id);
        $this->fmt->result($result);
    }
}
