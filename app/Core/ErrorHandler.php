<?php
declare(strict_types=1);

namespace App\Core;

class ErrorHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        self::log("ERROR [{$severity}]: {$message} in {$file}:{$line}");
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleException(\Throwable $e): void
    {
        self::log("EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());

        http_response_code(500);

        if ((new Request())->isAjax() || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            Response::json([
                'success' => false,
                'message' => 'An internal server error occurred.',
                'error' => getenv('APP_ENV') === 'development' ? $e->getMessage() : null,
            ], 500);
            return;
        }

        try {
            View::render('errors/500', [
                'title' => 'Server Error',
                'exception' => $e,
            ], 'main');
        } catch (\Throwable) {
            echo "<h1>500 Internal Server Error</h1><p>A fatal exception occurred. Details have been logged safely.</p>";
        }
    }

    private static function log(string $message): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/app_' . date('Y-m-d') . '.log';
        $entry = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
        error_log($entry, 3, $logFile);
    }
}
