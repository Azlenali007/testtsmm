<?php
declare(strict_types=1);

namespace App\Helpers;

class Url
{
    /**
     * Get base URL path if application runs in a subdirectory
     */
    public static function base(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = dirname($scriptName);
        if ($dir === '/' || $dir === '\\' || $dir === '.') {
            return '';
        }
        return rtrim(str_replace('\\', '/', $dir), '/');
    }

    /**
     * Generate an application URL with proper subdirectory prefix
     */
    public static function to(string $path = '/'): string
    {
        $base = self::base();
        $path = '/' . ltrim($path, '/');
        return $base . $path;
    }

    /**
     * Generate an asset URL with proper base path
     */
    public static function asset(string $path): string
    {
        return self::to($path);
    }
}
