<?php
declare(strict_types=1);

namespace App\Core;

class ThemeManager
{
    private static ?string $activeTheme = null;
    private static string $fallbackTheme = 'classic';
    private static string $themesDirectory = __DIR__ . '/../../resources/views/themes';

    public static function getActiveTheme(): string
    {
        if (self::$activeTheme !== null) {
            return self::$activeTheme;
        }

        try {
            $stmt = Database::pdo()->prepare("SELECT setting_value FROM settings WHERE setting_key = 'active_theme' LIMIT 1");
            $stmt->execute();
            $theme = $stmt->fetchColumn();
            if ($theme && self::themeExists((string)$theme)) {
                self::$activeTheme = (string)$theme;
                return self::$activeTheme;
            }
        } catch (\Throwable) {
            // fallback if db is not ready
        }

        self::$activeTheme = self::$fallbackTheme;
        return self::$activeTheme;
    }

    public static function setActiveTheme(string $themeName): bool
    {
        if (!self::themeExists($themeName)) {
            return false;
        }

        self::$activeTheme = $themeName;
        $stmt = Database::pdo()->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('active_theme', :theme)
            ON DUPLICATE KEY UPDATE setting_value = :theme_up
        ");
        return $stmt->execute([
            'theme' => $themeName,
            'theme_up' => $themeName,
        ]);
    }

    public static function themeExists(string $themeName): bool
    {
        $themeDir = self::$themesDirectory . '/' . $themeName;
        return is_dir($themeDir) && is_file($themeDir . '/layouts/main.php');
    }

    public static function getInstalledThemes(): array
    {
        $themes = [];
        $items = scandir(self::$themesDirectory);
        if (!$items) {
            return ['classic'];
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (self::themeExists($item)) {
                $themes[$item] = [
                    'id' => $item,
                    'name' => ucfirst($item) . ' Theme',
                    'path' => self::$themesDirectory . '/' . $item,
                    'is_active' => (self::getActiveTheme() === $item),
                ];
            }
        }

        return $themes;
    }

    public static function resolveViewPath(string $view): string
    {
        $theme = self::getActiveTheme();
        $target = self::$themesDirectory . '/' . $theme . '/pages/' . $view . '.php';

        if (is_file($target)) {
            return $target;
        }

        // Fallback to classic theme
        $fallback = self::$themesDirectory . '/' . self::$fallbackTheme . '/pages/' . $view . '.php';
        if (is_file($fallback)) {
            return $fallback;
        }

        // Shared views directory
        $shared = __DIR__ . '/../../resources/views/' . $view . '.php';
        if (is_file($shared)) {
            return $shared;
        }

        throw new \RuntimeException("View [{$view}] not found in theme [{$theme}] or fallback [classic].");
    }

    public static function resolveLayoutPath(string $layout = 'main'): string
    {
        $theme = self::getActiveTheme();
        $target = self::$themesDirectory . '/' . $theme . '/layouts/' . $layout . '.php';

        if (is_file($target)) {
            return $target;
        }

        $fallback = self::$themesDirectory . '/' . self::$fallbackTheme . '/layouts/' . $layout . '.php';
        if (is_file($fallback)) {
            return $fallback;
        }

        return __DIR__ . '/../../resources/views/layouts/' . $layout . '.php';
    }

    public static function resolveComponentPath(string $component): string
    {
        $theme = self::getActiveTheme();
        $target = self::$themesDirectory . '/' . $theme . '/components/' . $component . '.php';

        if (is_file($target)) {
            return $target;
        }

        $fallback = self::$themesDirectory . '/' . self::$fallbackTheme . '/components/' . $component . '.php';
        if (is_file($fallback)) {
            return $fallback;
        }

        return __DIR__ . '/../../resources/views/components/' . $component . '.php';
    }
}
