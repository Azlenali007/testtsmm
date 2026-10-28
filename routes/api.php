<?php
declare(strict_types=1);

/** @var \App\Core\Router $router */

use App\Controllers\ApiController;

// Standard SMM v2 Client API Endpoint
$router->post('/api/v2', [ApiController::class, 'handle']);
$router->get('/api/v2', [ApiController::class, 'handle']);
