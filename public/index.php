<?php
declare(strict_types=1);

// PHP Built-in Server Static Asset Passthrough
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    if (is_file($file)) {
        return false;
    }
}

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;

$router = new Router();

// Load Route Definitions
require_once __DIR__ . '/../routes/web.php';
require_once __DIR__ . '/../routes/admin.php';
require_once __DIR__ . '/../routes/api.php';

$request = new Request();
$router->dispatch($request);
