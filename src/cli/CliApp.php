<?php
declare(strict_types=1);

/**
 * CliApp - Command parser and dispatcher for the CLI tool.
 *
 * Usage:
 *   php bin/kindergartenctl <command> [args...] [--option=value ...]
 *
 * Examples:
 *   php bin/kindergartenctl games:list --sort=name
 *   php bin/kindergartenctl games:create --name="Memory"
 *   php bin/kindergartenctl games:show 5
 *   php bin/kindergartenctl search "memory"
 *   php bin/kindergartenctl health
 */
class CliApp
{
    private array $argv;
    private CliFormatter $fmt;

    /** @var array<string, array{class-string, string}> */
    private array $commands;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->fmt = new CliFormatter();
        $this->commands = $this->registerCommands();
    }

    /**
     * Parse arguments and dispatch to the appropriate command handler.
     */
    public function run(): void
    {
        $args = array_slice($this->argv, 1); // strip script name

        if (empty($args) || in_array($args[0], ['help', '--help', '-h'], true)) {
            $this->showHelp();
            return;
        }

        $command = $args[0];
        $rest = array_slice($args, 1);
        $parsed = $this->parseArgs($rest);

        if (!isset($this->commands[$command])) {
            $this->fmt->error("Unknown command: {$command}");
            $this->fmt->line('Run "php bin/kindergartenctl help" for available commands.');
            exit(1);
        }

        [$class, $method] = $this->commands[$command];

        // Auto-load command class
        $file = SRC_PATH . '/cli/commands/' . $class . '.php';
        if (!class_exists($class) && file_exists($file)) {
            require_once $file;
        }

        if (!class_exists($class)) {
            $this->fmt->error("Command class not found: {$class}");
            exit(1);
        }

        $handler = new $class($this->fmt);
        $handler->$method($parsed);
    }

    // ------------------------------------------------------------------
    // Argument parsing
    // ------------------------------------------------------------------

    /**
     * Parse CLI arguments into positional args and named options.
     *
     * @return array{args: string[], options: array<string, string|bool>}
     */
    private function parseArgs(array $raw): array
    {
        $args = [];
        $options = [];

        foreach ($raw as $token) {
            if (str_starts_with($token, '--')) {
                $stripped = substr($token, 2);
                if (str_contains($stripped, '=')) {
                    [$key, $value] = explode('=', $stripped, 2);
                    $options[$key] = $value;
                } else {
                    $options[$stripped] = true;
                }
            } else {
                $args[] = $token;
            }
        }

        return ['args' => $args, 'options' => $options];
    }

    // ------------------------------------------------------------------
    // Command registry
    // ------------------------------------------------------------------

    private function registerCommands(): array
    {
        return [
            // Games
            'games:list'      => ['GameCommand', 'list'],
            'games:show'      => ['GameCommand', 'show'],
            'games:create'    => ['GameCommand', 'create'],
            'games:update'    => ['GameCommand', 'update'],
            'games:delete'    => ['GameCommand', 'delete'],
            'games:duplicate' => ['GameCommand', 'duplicate'],

            // Materials
            'materials:list'   => ['MaterialCommand', 'list'],
            'materials:show'   => ['MaterialCommand', 'show'],
            'materials:create' => ['MaterialCommand', 'create'],
            'materials:update' => ['MaterialCommand', 'update'],
            'materials:delete' => ['MaterialCommand', 'delete'],

            // Boxes
            'boxes:list'   => ['BoxCommand', 'list'],
            'boxes:show'   => ['BoxCommand', 'show'],
            'boxes:create' => ['BoxCommand', 'create'],
            'boxes:update' => ['BoxCommand', 'update'],
            'boxes:delete' => ['BoxCommand', 'delete'],

            // Categories
            'categories:list'   => ['CategoryCommand', 'list'],
            'categories:show'   => ['CategoryCommand', 'show'],
            'categories:create' => ['CategoryCommand', 'create'],
            'categories:update' => ['CategoryCommand', 'update'],
            'categories:delete' => ['CategoryCommand', 'delete'],

            // Tags
            'tags:list'   => ['TagCommand', 'list'],
            'tags:show'   => ['TagCommand', 'show'],
            'tags:create' => ['TagCommand', 'create'],
            'tags:update' => ['TagCommand', 'update'],
            'tags:delete' => ['TagCommand', 'delete'],

            // Groups
            'groups:list'       => ['GroupCommand', 'list'],
            'groups:show'       => ['GroupCommand', 'show'],
            'groups:create'     => ['GroupCommand', 'create'],
            'groups:update'     => ['GroupCommand', 'update'],
            'groups:delete'     => ['GroupCommand', 'delete'],
            'groups:add-item'   => ['GroupCommand', 'addItem'],
            'groups:remove-item'=> ['GroupCommand', 'removeItem'],

            // Locations
            'locations:list'   => ['LocationCommand', 'list'],
            'locations:show'   => ['LocationCommand', 'show'],
            'locations:create' => ['LocationCommand', 'create'],
            'locations:update' => ['LocationCommand', 'update'],
            'locations:delete' => ['LocationCommand', 'delete'],

            // Calendar
            'calendar:list'   => ['CalendarCommand', 'list'],
            'calendar:create' => ['CalendarCommand', 'create'],
            'calendar:update' => ['CalendarCommand', 'update'],
            'calendar:delete' => ['CalendarCommand', 'delete'],

            // Search
            'search' => ['SearchCommand', 'search'],

            // Users
            'users:list'   => ['UserCommand', 'list'],
            'users:create' => ['UserCommand', 'create'],
            'users:delete' => ['UserCommand', 'delete'],

            // Settings
            'settings:show'   => ['SettingsCommand', 'show'],
            'settings:update' => ['SettingsCommand', 'update'],
            'settings:debug'  => ['SettingsCommand', 'debug'],

            // Changelog
            'changelog:list'  => ['ChangelogCommand', 'list'],
            'changelog:clear' => ['ChangelogCommand', 'clear'],

            // Database
            'db:migrate' => ['DbCommand', 'migrate'],

            // System
            'health' => ['HealthCommand', 'check'],
            'help'   => ['HealthCommand', 'help'],
        ];
    }

    // ------------------------------------------------------------------
    // Help
    // ------------------------------------------------------------------

    private function showHelp(): void
    {
        $this->fmt->line($this->fmt->bold('KindergartenCtl') . ' - Command-line management tool');
        $this->fmt->newline();
        $this->fmt->line('Usage: php bin/kindergartenctl <command> [arguments] [--options]');
        $this->fmt->newline();

        $groups = [
            'Entity Management' => [
                'games:list [--sort=name] [--order=asc] [--box=ID] [--category=ID] [--search=QUERY]',
                'games:show <id>',
                'games:create --name="..." [--description="..."] [--box=ID] [--category=ID]',
                'games:update <id> [--name="..."] [--description="..."]',
                'games:delete <id> [--force]',
                'games:duplicate <id>',
                '',
                'materials:list / materials:show / materials:create / materials:update / materials:delete',
                'boxes:list / boxes:show / boxes:create / boxes:update / boxes:delete',
                'categories:list / categories:show / categories:create / categories:update / categories:delete',
                'tags:list / tags:show / tags:create / tags:update / tags:delete',
                'groups:list / groups:show / groups:create / groups:update / groups:delete',
                'groups:add-item --group=ID --type=game|material --item=ID',
                'groups:remove-item --group=ID --type=game|material --item=ID',
                'locations:list / locations:show / locations:create / locations:update / locations:delete',
            ],
            'Calendar' => [
                'calendar:list [--start=YYYY-MM-DD] [--end=YYYY-MM-DD]',
                'calendar:create --title="..." --start=YYYY-MM-DD [--end=...] [--color=#...]',
                'calendar:update <id> [--title="..."]',
                'calendar:delete <id>',
            ],
            'Search' => [
                'search <query> [--type=games|materials|boxes|tags|groups]',
            ],
            'Users' => [
                'users:list',
                'users:create --name="..." --email="..." --password="..."',
                'users:delete <id> [--force]',
            ],
            'Settings & Data' => [
                'settings:show',
                'settings:update [--language=de|en] [--items-per-page=20]',
                'settings:debug [on|off]',
                'changelog:list [--type=game] [--limit=50]',
                'changelog:clear [--force]',
            ],
            'System' => [
                'db:migrate',
                'health',
                'help',
            ],
        ];

        foreach ($groups as $group => $cmds) {
            $this->fmt->line($this->fmt->bold($group));
            foreach ($cmds as $cmd) {
                $this->fmt->line('  ' . $cmd);
            }
            $this->fmt->newline();
        }
    }
}
