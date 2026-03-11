<?php
declare(strict_types=1);

class SettingsService
{
    public function getPreferences(): array
    {
        $prefsFile = STORAGE_PATH . '/preferences.php';
        if (file_exists($prefsFile)) {
            return require $prefsFile;
        }
        return [];
    }

    public function updateLanguage(string $language): ServiceResult
    {
        $allowed = ['de', 'en'];
        if (!in_array($language, $allowed, true)) {
            $language = 'de';
        }

        $prefs = $this->getPreferences();
        $prefs['language'] = $language;
        $this->savePreferences($prefs);

        return ServiceResult::ok(['language' => $language], __('settings.language_changed'));
    }

    public function updateCustomization(string $themeColor, string $themePattern): ServiceResult
    {
        // Validate color
        if (!empty($themeColor) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $themeColor)) {
            $themeColor = '';
        }

        // Validate pattern
        $allowedPatterns = ['none', 'dots', 'stars', 'hearts', 'clouds'];
        if (!in_array($themePattern, $allowedPatterns, true)) {
            $themePattern = 'none';
        }

        $prefs = $this->getPreferences();
        $prefs['theme_color'] = $themeColor;
        $prefs['theme_pattern'] = $themePattern;
        $this->savePreferences($prefs);

        return ServiceResult::ok([], __('flash.design_updated'));
    }

    public function toggleDebug(): ServiceResult
    {
        $debugFlagPath = ROOT_PATH . '/storage/debug.flag';

        if (file_exists($debugFlagPath)) {
            unlink($debugFlagPath);
            return ServiceResult::ok(['debug' => false], __('settings.debug_mode_disabled'));
        } else {
            file_put_contents($debugFlagPath, date('Y-m-d H:i:s'));
            return ServiceResult::ok(['debug' => true], __('settings.debug_mode_enabled'));
        }
    }

    public function setDebug(bool $enabled): ServiceResult
    {
        $debugFlagPath = ROOT_PATH . '/storage/debug.flag';
        $current = file_exists($debugFlagPath);

        if ($enabled && !$current) {
            file_put_contents($debugFlagPath, date('Y-m-d H:i:s'));
            return ServiceResult::ok(['debug' => true], __('settings.debug_mode_enabled'));
        } elseif (!$enabled && $current) {
            unlink($debugFlagPath);
            return ServiceResult::ok(['debug' => false], __('settings.debug_mode_disabled'));
        }

        return ServiceResult::ok(['debug' => $enabled]);
    }

    public function toggleDarkMode(string $preference): ServiceResult
    {
        $allowed = ['system', 'light', 'dark'];
        if (!in_array($preference, $allowed, true)) {
            $preference = 'system';
        }

        $prefs = $this->getPreferences();
        $prefs['dark_mode_preference'] = $preference;
        $this->savePreferences($prefs);

        return ServiceResult::ok(['preference' => $preference]);
    }

    public function updatePreferences(array $newPrefs): ServiceResult
    {
        $prefs = $this->getPreferences();
        $prefs = array_merge($prefs, $newPrefs);
        $this->savePreferences($prefs);

        return ServiceResult::ok($prefs);
    }

    private function savePreferences(array $config): bool
    {
        $configPath = STORAGE_PATH . '/preferences.php';
        $content = "<?php\nreturn " . var_export($config, true) . ";\n";

        if (file_put_contents($configPath, $content, LOCK_EX) === false) {
            Logger::error('Failed to save preferences', ['path' => $configPath]);
            return false;
        }
        return true;
    }
}
