<?php
declare(strict_types=1);

class BoxCommand
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

        $result = (new BoxService())->list($sort, $dir);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $rows = [];
        foreach ($result->data['boxes'] as $box) {
            $rows[] = [
                'ID'        => $box['id'],
                'Name'      => $box['name'],
                'Number'    => $box['number'] ?? '-',
                'Label'     => $box['label'] ?? '-',
                'Materials' => $box['material_count'] ?? 0,
            ];
        }

        $this->fmt->table(['ID', 'Name', 'Number', 'Label', 'Materials'], $rows);
    }

    public function show(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid box ID.');
            return;
        }

        $result = (new BoxService())->get($id);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $box = $result->data['box'];

        $this->fmt->detail([
            'id'          => $box['id'] ?? '',
            'name'        => $box['name'] ?? '',
            'number'      => $box['number'] ?? '-',
            'label'       => $box['label'] ?? '-',
            'location'    => $box['location_name'] ?? '-',
            'description' => $box['description'] ?? '',
            'notes'       => $box['notes'] ?? '',
            'created_at'  => $box['created_at'] ?? '',
        ], [
            'id'          => 'ID',
            'name'        => 'Name',
            'number'      => 'Number',
            'label'       => 'Label',
            'location'    => 'Location',
            'description' => 'Description',
            'notes'       => 'Notes',
            'created_at'  => 'Created',
        ]);

        if (!empty($result->data['materials'])) {
            $this->fmt->newline();
            $this->fmt->info('Materials in this box:');
            $matRows = [];
            foreach ($result->data['materials'] as $mat) {
                $matRows[] = ['ID' => $mat['id'], 'Name' => $mat['name']];
            }
            $this->fmt->table(['ID', 'Name'], $matRows);
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
        if (isset($options['number'])) $data['number'] = (string) $options['number'];
        if (isset($options['label'])) $data['label'] = (string) $options['label'];
        if (isset($options['location'])) $data['location_id'] = (int) $options['location'];
        if (isset($options['description'])) $data['description'] = (string) $options['description'];
        if (isset($options['notes'])) $data['notes'] = (string) $options['notes'];

        $result = (new BoxService())->create($data);
        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New box ID: ' . $result->data['id']);
        }
    }

    public function update(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid box ID.');
            return;
        }

        $options = $parsed['options'];
        $data = [];
        if (isset($options['name'])) $data['name'] = (string) $options['name'];
        if (isset($options['number'])) $data['number'] = (string) $options['number'];
        if (isset($options['label'])) $data['label'] = (string) $options['label'];
        if (isset($options['location'])) $data['location_id'] = (int) $options['location'];
        if (isset($options['description'])) $data['description'] = (string) $options['description'];
        if (isset($options['notes'])) $data['notes'] = (string) $options['notes'];

        if (empty($data)) {
            $this->fmt->warn('No fields to update. Use --name, --number, --label, etc.');
            return;
        }

        $result = (new BoxService())->update($id, $data);
        $this->fmt->result($result);
    }

    public function delete(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid box ID.');
            return;
        }

        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to delete box #' . $id . '?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $result = (new BoxService())->delete($id);
        $this->fmt->result($result);
    }
}
