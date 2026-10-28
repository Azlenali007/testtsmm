<?php
declare(strict_types=1);

/** @var \App\Core\Router $router */

use App\Controllers\AccountController;
use App\Controllers\AffiliateController;
use App\Controllers\AuthController;
use App\Controllers\ChildPanelController;
use App\Controllers\CouponController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\OrderController;
use App\Controllers\PaymentController;
use App\Controllers\ServiceController;
use App\Controllers\TicketController;
use App\Controllers\WalletController;

// 1. Public & Guest Routes
$router->get('/', [HomeController::class, 'index']);
$router->get('/index.html', [HomeController::class, 'index']);
$router->get('/index.php', [HomeController::class, 'index']);
$router->get('/terms', [HomeController::class, 'terms']);
$router->get('/privacy', [HomeController::class, 'privacy']);
$router->get('/services', [ServiceController::class, 'index']);
$router->get('/services/detail/{id}', [ServiceController::class, 'detail']);

$router->group(['middleware' => ['guest']], function ($router) {
    // Login / Sign In
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->get('/signin', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login'], ['csrf']);
    $router->post('/signin', [AuthController::class, 'login'], ['csrf']);

    // Register / Sign Up
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->get('/signup', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register'], ['csrf']);
    $router->post('/signup', [AuthController::class, 'register'], ['csrf']);

    // Password Recovery
    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
    $router->get('/forgot', [AuthController::class, 'showForgotPassword']);
    $router->get('/password/forgot', [AuthController::class, 'showForgotPassword']);
    $router->post('/forgot-password', [AuthController::class, 'forgotPassword'], ['csrf']);
    $router->post('/forgot', [AuthController::class, 'forgotPassword'], ['csrf']);
    $router->get('/reset-password', [AuthController::class, 'showResetPassword']);
    $router->get('/password/reset', [AuthController::class, 'showResetPassword']);
    $router->post('/reset-password', [AuthController::class, 'resetPassword'], ['csrf']);
    $router->post('/password/reset', [AuthController::class, 'resetPassword'], ['csrf']);
});

$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/signout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// 2. Protected Authenticated User Routes
$router->group(['middleware' => ['auth']], function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);

    // Orders
    $router->get('/order/new', [OrderController::class, 'newOrder']);
    $router->post('/order/new', [OrderController::class, 'store'], ['csrf']);
    $router->post('/order/store', [OrderController::class, 'store'], ['csrf']);
    $router->get('/order/mass', [OrderController::class, 'massOrder']);
    $router->get('/orders/mass', [OrderController::class, 'massOrder']);
    $router->get('/mass-order', [OrderController::class, 'massOrder']);
    $router->post('/order/mass', [OrderController::class, 'storeMass'], ['csrf']);
    $router->post('/orders/mass', [OrderController::class, 'storeMass'], ['csrf']);
    $router->post('/mass-order', [OrderController::class, 'storeMass'], ['csrf']);
    $router->get('/orders', [OrderController::class, 'index']);
    $router->post('/order/refill', [OrderController::class, 'requestRefill'], ['csrf']);
    $router->post('/order/{id}/refill', [OrderController::class, 'requestRefill'], ['csrf']);
    $router->post('/order/{id}/cancel', [OrderController::class, 'cancelOrder'], ['csrf']);

    // Wallet & Payments
    $router->get('/wallet', [WalletController::class, 'index']);
    $router->post('/wallet/deposit', [PaymentController::class, 'initiate'], ['csrf']);
    $router->post('/payment/initiate', [PaymentController::class, 'initiate'], ['csrf']);
    $router->get('/payment/checkout/{code}', [PaymentController::class, 'showCheckout']);
    $router->post('/payment/bank-proof', [PaymentController::class, 'submitBankProof'], ['csrf']);
    $router->post('/payment/submit-proof', [PaymentController::class, 'submitBankProof'], ['csrf']);
    $router->post('/payment/simulate-success', [PaymentController::class, 'simulateSuccess'], ['csrf']);
    $router->post('/coupon/redeem', [CouponController::class, 'redeem'], ['csrf']);

    // Support Tickets
    $router->get('/tickets', [TicketController::class, 'index']);
    $router->get('/tickets/create', [TicketController::class, 'create']);
    $router->get('/tickets/new', [TicketController::class, 'create']);
    $router->post('/tickets/store', [TicketController::class, 'store'], ['csrf']);
    $router->get('/tickets/{id}', [TicketController::class, 'show']);
    $router->post('/tickets/{id}/reply', [TicketController::class, 'reply'], ['csrf']);

    // Account & API
    $router->get('/account', [AccountController::class, 'index']);
    $router->post('/account/password', [AccountController::class, 'updatePassword'], ['csrf']);
    $router->post('/account/timezone', [AccountController::class, 'updateTimezone'], ['csrf']);
    $router->post('/account/regenerate-api', [AccountController::class, 'regenerateApiKey'], ['csrf']);
    $router->get('/api-docs', [AccountController::class, 'apiDocs']);

    // Affiliates / Referrals
    $router->get('/affiliates', [AffiliateController::class, 'index']);
    $router->post('/affiliates/payout', [AffiliateController::class, 'requestPayout'], ['csrf']);

    // Child Panels
    $router->get('/child-panels', [ChildPanelController::class, 'index']);
    $router->post('/child-panels/order', [ChildPanelController::class, 'store'], ['csrf']);
});

// 3. Payment Webhook Handlers
$router->post('/payment/webhook/{gateway}', [PaymentController::class, 'webhook']);
$router->get('/payment/webhook/{gateway}', [PaymentController::class, 'webhook']);
