<?php
declare(strict_types=1);

class UserCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function list(array $parsed): void
    {
        $result = (new UserService())->list();

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $rows = [];
        foreach ($result->data['users'] as $user) {
            $rows[] = [
                'ID'       => $user['id'],
                'Username' => $user['username'],
                'Email'    => $user['email'],
                'Created'  => $user['created_at'] ?? '',
            ];
        }

        $this->fmt->table(['ID', 'Username', 'Email', 'Created'], $rows);
    }

    public function create(array $parsed): void
    {
        $options = $parsed['options'];

        if (empty($options['name'])) {
            $this->fmt->error('The --name option is required.');
            return;
        }
        if (empty($options['email'])) {
            $this->fmt->error('The --email option is required.');
            return;
        }
        if (empty($options['password'])) {
            $this->fmt->error('The --password option is required.');
            return;
        }

        $result = (new UserService())->createUser(
            (string) $options['name'],
            (string) $options['email'],
            (string) $options['password']
        );

        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New user ID: ' . $result->data['id']);
        }
    }

    public function delete(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid user ID.');
            return;
        }

        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to delete user #' . $id . '?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $result = (new UserService())->deleteUser($id);
        $this->fmt->result($result);
    }
}
