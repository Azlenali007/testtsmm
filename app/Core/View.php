<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    private static array $sharedData = [];

    public static function share(string $key, mixed $value): void
    {
        self::$sharedData[$key] = $value;
    }

    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $mergedData = array_merge(self::$sharedData, [
            'currentUser' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
            'csrf_field' => Csrf::field(),
            'csrf_token' => Csrf::token(),
            'activeTheme' => ThemeManager::getActiveTheme(),
        ], $data);

        extract($mergedData);

        $viewFile = ThemeManager::resolveViewPath($view);
        
        // Capture view output
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // If layout is empty or 'none', print raw content
        if ($layout === 'none' || empty($layout)) {
            echo $content;
            return;
        }

        $layoutFile = ThemeManager::resolveLayoutPath($layout);
        if (is_file($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function component(string $name, array $data = []): void
    {
        extract(array_merge(self::$sharedData, $data));
        $file = ThemeManager::resolveComponentPath($name);
        if (is_file($file)) {
            require $file;
        }
    }
}
