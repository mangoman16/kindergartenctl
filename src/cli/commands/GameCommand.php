<?php
declare(strict_types=1);

/**
 * GameCommand - CLI handler for game management.
 */
class GameCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    /**
     * games:list [--sort=name] [--order=asc] [--box=ID] [--category=ID] [--tag=ID]
     *            [--outdoor] [--active] [--favorite] [--search=QUERY]
     */
    public function list(array $parsed): void
    {
        $options = $parsed['options'];

        $filters = [];
        if (isset($options['box'])) {
            $filters['box_id'] = (int) $options['box'];
        }
        if (isset($options['category'])) {
            $filters['category_id'] = (int) $options['category'];
        }
        if (isset($options['tag'])) {
            $filters['tag_id'] = (int) $options['tag'];
        }
        if (isset($options['outdoor'])) {
            $filters['is_outdoor'] = 1;
        }
        if (isset($options['active'])) {
            $filters['is_active'] = 1;
        }
        if (isset($options['favorite'])) {
            $filters['is_favorite'] = 1;
        }
        if (isset($options['search'])) {
            $filters['search'] = (string) $options['search'];
        }

        $sort = (string) ($options['sort'] ?? 'name');
        $order = (string) ($options['order'] ?? 'asc');

        $service = new GameService();
        $result = $service->list($filters, $sort, $order);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $rows = [];
        foreach ($result->data['games'] as $game) {
            $rows[] = [
                'ID'       => $game['id'],
                'Name'     => $game['name'],
                'Box'      => $game['box_name'] ?? '-',
                'Category' => $game['category_name'] ?? '-',
                'Active'   => !empty($game['is_active']) ? 'Yes' : 'No',
                'Favorite' => !empty($game['is_favorite']) ? 'Yes' : 'No',
            ];
        }

        $this->fmt->table(['ID', 'Name', 'Box', 'Category', 'Active', 'Favorite'], $rows);
    }

    /**
     * games:show <id>
     */
    public function show(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid game ID.');
            return;
        }

        $service = new GameService();
        $result = $service->get($id);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $game = $result->data['game'];

        $this->fmt->detail([
            'id'               => $game['id'] ?? '',
            'name'             => $game['name'] ?? '',
            'description'      => $game['description'] ?? '',
            'instructions'     => $game['instructions'] ?? '',
            'min_players'      => $game['min_players'] ?? '',
            'max_players'      => $game['max_players'] ?? '',
            'duration_minutes' => $game['duration_minutes'] ?? '',
            'difficulty'       => $game['difficulty'] ?? '',
            'is_outdoor'       => !empty($game['is_outdoor']) ? 'Yes' : 'No',
            'is_active'        => !empty($game['is_active']) ? 'Yes' : 'No',
            'is_favorite'      => !empty($game['is_favorite']) ? 'Yes' : 'No',
            'box'              => $game['box_name'] ?? '-',
            'category'         => $game['category_name'] ?? '-',
            'tags'             => !empty($game['tags']) ? implode(', ', array_column($game['tags'], 'name')) : '-',
            'image_path'       => $game['image_path'] ?? '-',
            'created_at'       => $game['created_at'] ?? '',
            'updated_at'       => $game['updated_at'] ?? '',
        ], [
            'id'               => 'ID',
            'name'             => 'Name',
            'description'      => 'Description',
            'instructions'     => 'Instructions',
            'min_players'      => 'Min Players',
            'max_players'      => 'Max Players',
            'duration_minutes' => 'Duration (min)',
            'difficulty'       => 'Difficulty',
            'is_outdoor'       => 'Outdoor',
            'is_active'        => 'Active',
            'is_favorite'      => 'Favorite',
            'box'              => 'Box',
            'category'         => 'Category',
            'tags'             => 'Tags',
            'image_path'       => 'Image',
            'created_at'       => 'Created',
            'updated_at'       => 'Updated',
        ]);
    }

    /**
     * games:create --name="..." [--description="..."] [--box=ID] [--category=ID]
     *              [--tags=1,2,3] [--outdoor] [--active]
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
        if (isset($options['instructions'])) {
            $data['instructions'] = (string) $options['instructions'];
        }
        if (isset($options['box'])) {
            $data['box_id'] = (int) $options['box'];
        }
        if (isset($options['category'])) {
            $data['category_id'] = (int) $options['category'];
        }
        if (isset($options['outdoor'])) {
            $data['is_outdoor'] = 1;
        }
        if (isset($options['active'])) {
            $data['is_active'] = 1;
        }

        $tagIds = [];
        if (isset($options['tags'])) {
            $tagIds = array_map('intval', explode(',', (string) $options['tags']));
        }

        $service = new GameService();
        $result = $service->create($data, $tagIds);

        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New game ID: ' . $result->data['id']);
        }
    }

    /**
     * games:update <id> --name="..." [--description="..."]
     */
    public function update(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid game ID.');
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
        if (isset($options['instructions'])) {
            $data['instructions'] = (string) $options['instructions'];
        }
        if (isset($options['box'])) {
            $data['box_id'] = (int) $options['box'];
        }
        if (isset($options['category'])) {
            $data['category_id'] = (int) $options['category'];
        }
        if (isset($options['outdoor'])) {
            $data['is_outdoor'] = 1;
        }
        if (isset($options['active'])) {
            $data['is_active'] = 1;
        }

        if (empty($data)) {
            $this->fmt->warn('No fields to update. Use --name, --description, etc.');
            return;
        }

        $tagIds = [];
        if (isset($options['tags'])) {
            $tagIds = array_map('intval', explode(',', (string) $options['tags']));
        }

        $service = new GameService();
        $result = $service->update($id, $data, $tagIds);

        $this->fmt->result($result);
    }

    /**
     * games:delete <id> [--force]
     */
    public function delete(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid game ID.');
            return;
        }

        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to delete game #' . $id . '?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $service = new GameService();
        $result = $service->delete($id);

        $this->fmt->result($result);
    }

    /**
     * games:duplicate <id>
     */
    public function duplicate(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid game ID.');
            return;
        }

        $service = new GameService();
        $result = $service->duplicate($id);

        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New game ID: ' . $result->data['id']);
        }
    }
}
