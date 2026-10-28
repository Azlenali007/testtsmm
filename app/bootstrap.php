<?php
declare(strict_types=1);

// SMM Panel Core Application Bootstrap

// 1. PSR-4 Compliant Autoloader
spl_autoload_register(function (string $class) {
    $prefixes = [
        'App\\' => __DIR__ . '/',
        'Modules\\' => __DIR__ . '/../modules/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 2. Global Error & Exception Handling
\App\Core\ErrorHandler::register();

// 3. Secure Session Initialization
\App\Core\Session::start();
