<?php
declare(strict_types=1);

/**
 * MaterialCommand - CLI handler for material management.
 */
class MaterialCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    /**
     * materials:list [--sort=name] [--order=asc] [--favorites] [--search=QUERY]
     */
    public function list(array $parsed): void
    {
        $options = $parsed['options'];

        $sort = (string) ($options['sort'] ?? 'name');
        $order = (string) ($options['order'] ?? 'asc');

        $filters = [];
        if (isset($options['favorites'])) {
            $filters['is_favorite'] = 1;
        }
        if (isset($options['search'])) {
            $filters['search'] = (string) $options['search'];
        }

        $service = new MaterialService();
        $result = $service->list($sort, $order, $filters);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $rows = [];
        foreach ($result->data['materials'] as $material) {
            $rows[] = [
                'ID'       => $material['id'],
                'Name'     => $material['name'],
                'Quantity' => $material['quantity'] ?? '-',
                'Games'    => $material['game_count'] ?? 0,
                'Favorite' => !empty($material['is_favorite']) ? 'Yes' : 'No',
            ];
        }

        $this->fmt->table(['ID', 'Name', 'Quantity', 'Games', 'Favorite'], $rows);
    }

    /**
     * materials:show <id>
     */
    public function show(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid material ID.');
            return;
        }

        $service = new MaterialService();
        $result = $service->get($id);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $material = $result->data['material'];

        $this->fmt->detail([
            'id'            => $material['id'] ?? '',
            'name'          => $material['name'] ?? '',
            'description'   => $material['description'] ?? '',
            'quantity'      => $material['quantity'] ?? '',
            'is_consumable' => !empty($material['is_consumable']) ? 'Yes' : 'No',
            'is_favorite'   => !empty($material['is_favorite']) ? 'Yes' : 'No',
            'game_count'    => $material['game_count'] ?? 0,
            'image_path'    => $material['image_path'] ?? '-',
            'created_at'    => $material['created_at'] ?? '',
            'updated_at'    => $material['updated_at'] ?? '',
        ], [
            'id'            => 'ID',
            'name'          => 'Name',
            'description'   => 'Description',
            'quantity'      => 'Quantity',
            'is_consumable' => 'Consumable',
            'is_favorite'   => 'Favorite',
            'game_count'    => 'Used in Games',
            'image_path'    => 'Image',
            'created_at'    => 'Created',
            'updated_at'    => 'Updated',
        ]);

        // Show associated games if any
        if (!empty($result->data['games'])) {
            $this->fmt->newline();
            $this->fmt->info('Associated Games:');
            $gameRows = [];
            foreach ($result->data['games'] as $game) {
                $gameRows[] = [
                    'ID'   => $game['id'],
                    'Name' => $game['name'],
                ];
            }
            $this->fmt->table(['ID', 'Name'], $gameRows);
        }
    }

    /**
     * materials:create --name="..." [--description="..."] [--quantity=N]
     */
    public function create(array $parsed): void
    {
        $options = $parsed['options'];

        if (empty($options['name'])) {
            $this->fmt->error('The --name option is required.');
            return;
        }

        $data = [
            'name' => (string) $options['name'],
        ];

        if (isset($options['description'])) {
            $data['description'] = (string) $options['description'];
        }
        if (isset($options['quantity'])) {
            $data['quantity'] = (int) $options['quantity'];
        }

        $service = new MaterialService();
        $result = $service->create($data);

        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New material ID: ' . $result->data['id']);
        }
    }

    /**
     * materials:update <id> [--name="..."] [--description="..."]
     */
    public function update(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid material ID.');
            return;
        }

        $options = $parsed['options'];
        $data = [];

        if (isset($options['name'])) {
            $data['name'] = (string) $options['name'];
        }
        if (isset($options['description'])) {
            $data['description'] = (string) $options['description'];
        }
        if (isset($options['quantity'])) {
            $data['quantity'] = (int) $options['quantity'];
        }

        if (empty($data)) {
            $this->fmt->warn('No fields to update. Use --name, --description, --quantity.');
            return;
        }

        $service = new MaterialService();
        $result = $service->update($id, $data);

        $this->fmt->result($result);
    }

    /**
     * materials:delete <id> [--force]
     */
    public function delete(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid material ID.');
            return;
        }

        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to delete material #' . $id . '?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $service = new MaterialService();
        $result = $service->delete($id);

        $this->fmt->result($result);
    }
}
