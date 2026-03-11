<?php
declare(strict_types=1);

class GroupCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function list(array $parsed): void
    {
        $result = (new GroupService())->list();

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $rows = [];
        foreach ($result->data['groups'] as $group) {
            $rows[] = [
                'ID'        => $group['id'],
                'Name'      => $group['name'],
                'Games'     => $group['game_count'] ?? 0,
                'Materials' => $group['material_count'] ?? 0,
            ];
        }

        $this->fmt->table(['ID', 'Name', 'Games', 'Materials'], $rows);
    }

    public function show(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid group ID.');
            return;
        }

        $result = (new GroupService())->get($id);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $group = $result->data['group'];

        $this->fmt->detail([
            'id'             => $group['id'] ?? '',
            'name'           => $group['name'] ?? '',
            'description'    => $group['description'] ?? '',
            'game_count'     => $group['game_count'] ?? 0,
            'material_count' => $group['material_count'] ?? 0,
            'created_at'     => $group['created_at'] ?? '',
        ], [
            'id'             => 'ID',
            'name'           => 'Name',
            'description'    => 'Description',
            'game_count'     => 'Games',
            'material_count' => 'Materials',
            'created_at'     => 'Created',
        ]);

        if (!empty($result->data['games'])) {
            $this->fmt->newline();
            $this->fmt->info('Games:');
            $rows = [];
            foreach ($result->data['games'] as $game) {
                $rows[] = ['ID' => $game['id'], 'Name' => $game['name']];
            }
            $this->fmt->table(['ID', 'Name'], $rows);
        }

        if (!empty($result->data['materials'])) {
            $this->fmt->newline();
            $this->fmt->info('Materials:');
            $rows = [];
            foreach ($result->data['materials'] as $mat) {
                $rows[] = ['ID' => $mat['id'], 'Name' => $mat['name']];
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

        $result = (new GroupService())->create($data);
        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New group ID: ' . $result->data['id']);
        }
    }

    public function update(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid group ID.');
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

        $result = (new GroupService())->update($id, $data);
        $this->fmt->result($result);
    }

    public function delete(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid group ID.');
            return;
        }

        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to delete group #' . $id . '?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $result = (new GroupService())->delete($id);
        $this->fmt->result($result);
    }

    public function addItem(array $parsed): void
    {
        $options = $parsed['options'];

        $groupId = (int) ($options['group'] ?? 0);
        $type = (string) ($options['type'] ?? '');
        $itemId = (int) ($options['item'] ?? 0);

        if ($groupId <= 0 || empty($type) || $itemId <= 0) {
            $this->fmt->error('Required: --group=ID --type=game|material --item=ID');
            return;
        }

        $result = (new GroupService())->addItem($groupId, $type, $itemId);
        $this->fmt->result($result);
    }

    public function removeItem(array $parsed): void
    {
        $options = $parsed['options'];

        $groupId = (int) ($options['group'] ?? 0);
        $type = (string) ($options['type'] ?? '');
        $itemId = (int) ($options['item'] ?? 0);

        if ($groupId <= 0 || empty($type) || $itemId <= 0) {
            $this->fmt->error('Required: --group=ID --type=game|material --item=ID');
            return;
        }

        $result = (new GroupService())->removeItem($groupId, $type, $itemId);
        $this->fmt->result($result);
    }
}
