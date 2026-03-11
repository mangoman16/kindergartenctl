<?php
declare(strict_types=1);

class SettingsCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function show(array $parsed): void
    {
        $service = new SettingsService();
        $prefs = $service->getPreferences();

        $this->fmt->detail([
            'language'             => $prefs['language'] ?? 'de',
            'theme_color'          => $prefs['theme_color'] ?? '-',
            'theme_pattern'        => $prefs['theme_pattern'] ?? 'none',
            'items_per_page'       => $prefs['items_per_page'] ?? '20',
            'default_view'         => $prefs['default_view'] ?? '-',
            'dark_mode_preference' => $prefs['dark_mode_preference'] ?? 'system',
        ], [
            'language'             => 'Language',
            'theme_color'          => 'Theme Color',
            'theme_pattern'        => 'Theme Pattern',
            'items_per_page'       => 'Items Per Page',
            'default_view'         => 'Default View',
            'dark_mode_preference' => 'Dark Mode',
        ]);

        $debugFlag = file_exists(ROOT_PATH . '/storage/debug.flag');
        $this->fmt->newline();
        $this->fmt->info('Debug mode: ' . ($debugFlag ? 'ON' : 'OFF'));
    }

    public function update(array $parsed): void
    {
        $options = $parsed['options'];
        $service = new SettingsService();

        if (isset($options['language'])) {
            $result = $service->updateLanguage((string) $options['language']);
            $this->fmt->result($result);
        }

        if (isset($options['items-per-page'])) {
            $result = $service->updatePreferences(['items_per_page' => (int) $options['items-per-page']]);
            $this->fmt->result($result);
        }

        if (isset($options['dark-mode'])) {
            $result = $service->toggleDarkMode((string) $options['dark-mode']);
            $this->fmt->result($result);
        }

        if (!isset($options['language']) && !isset($options['items-per-page']) && !isset($options['dark-mode'])) {
            $this->fmt->warn('No settings to update. Use --language, --items-per-page, --dark-mode.');
        }
    }

    public function debug(array $parsed): void
    {
        $arg = $parsed['args'][0] ?? '';
        $service = new SettingsService();

        if ($arg === 'on') {
            $result = $service->setDebug(true);
        } elseif ($arg === 'off') {
            $result = $service->setDebug(false);
        } else {
            $result = $service->toggleDebug();
        }

        $this->fmt->result($result);
    }
}
