<?php
declare(strict_types=1);

/** @var \App\Core\Router $router */

use App\Controllers\Admin\AdminBroadcastController;
use App\Controllers\Admin\AdminChildPanelController;
use App\Controllers\Admin\AdminCouponController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminLogController;
use App\Controllers\Admin\AdminOrderController;
use App\Controllers\Admin\AdminPaymentController;
use App\Controllers\Admin\AdminProviderController;
use App\Controllers\Admin\AdminReferralController;
use App\Controllers\Admin\AdminServiceController;
use App\Controllers\Admin\AdminSettingController;
use App\Controllers\Admin\AdminTicketController;
use App\Controllers\Admin\AdminUserController;

$router->group(['prefix' => '/admin', 'middleware' => ['admin']], function ($router) {
    $router->get('/', [AdminDashboardController::class, 'index']);

    // Users
    $router->get('/users', [AdminUserController::class, 'index']);
    $router->get('/users/edit/{id}', [AdminUserController::class, 'edit']);
    $router->post('/users/edit/{id}', [AdminUserController::class, 'update'], ['csrf']);
    $router->post('/users/update/{id}', [AdminUserController::class, 'update'], ['csrf']);
    $router->post('/users/adjust-balance', [AdminUserController::class, 'adjustBalance'], ['csrf']);
    $router->post('/users/adjust-balance/{id}', [AdminUserController::class, 'adjustBalance'], ['csrf']);

    // Services & Categories
    $router->get('/services', [AdminServiceController::class, 'index']);
    $router->post('/services/store', [AdminServiceController::class, 'storeService'], ['csrf']);
    $router->post('/services/delete/{id}', [AdminServiceController::class, 'deleteService'], ['csrf']);
    $router->post('/categories/store', [AdminServiceController::class, 'storeCategory'], ['csrf']);
    $router->post('/categories/delete/{id}', [AdminServiceController::class, 'deleteCategory'], ['csrf']);
    $router->post('/services/bulk-margin', [AdminServiceController::class, 'bulkMargin'], ['csrf']);

    // Orders
    $router->get('/orders', [AdminOrderController::class, 'index']);
    $router->post('/orders/update/{id}', [AdminOrderController::class, 'updateStatus'], ['csrf']);
    $router->post('/orders/resend/{id}', [AdminOrderController::class, 'resendToProvider'], ['csrf']);

    // Providers
    $router->get('/providers', [AdminProviderController::class, 'index']);
    $router->post('/providers/store', [AdminProviderController::class, 'store'], ['csrf']);
    $router->post('/providers/sync/{id}', [AdminProviderController::class, 'sync'], ['csrf']);
    $router->get('/providers/test-balance/{id}', [AdminProviderController::class, 'testBalance']);
    $router->post('/providers/test-balance/{id}', [AdminProviderController::class, 'testBalance'], ['csrf']);
    $router->post('/providers/delete/{id}', [AdminProviderController::class, 'delete'], ['csrf']);

    // Payments
    $router->get('/payments', [AdminPaymentController::class, 'index']);
    $router->post('/payments/approve/{id}', [AdminPaymentController::class, 'approve'], ['csrf']);
    $router->post('/payments/reject/{id}', [AdminPaymentController::class, 'reject'], ['csrf']);
    $router->get('/payments/settings', [AdminPaymentController::class, 'settings']);
    $router->get('/payment-methods', [AdminPaymentController::class, 'settings']);
    $router->get('/payments/methods', [AdminPaymentController::class, 'settings']);
    $router->post('/payments/settings/{id}', [AdminPaymentController::class, 'updateMethod'], ['csrf']);
    $router->post('/payments/settings/update/{id}', [AdminPaymentController::class, 'updateMethod'], ['csrf']);

    // Tickets
    $router->get('/tickets', [AdminTicketController::class, 'index']);
    $router->get('/tickets/{id}', [AdminTicketController::class, 'show']);
    $router->post('/tickets/{id}/reply', [AdminTicketController::class, 'reply'], ['csrf']);
    $router->post('/tickets/reply/{id}', [AdminTicketController::class, 'reply'], ['csrf']);
    $router->post('/tickets/{id}/close', [AdminTicketController::class, 'close'], ['csrf']);
    $router->post('/tickets/close/{id}', [AdminTicketController::class, 'close'], ['csrf']);

    // Coupons
    $router->get('/coupons', [AdminCouponController::class, 'index']);
    $router->post('/coupons/store', [AdminCouponController::class, 'store'], ['csrf']);
    $router->post('/coupons/delete/{id}', [AdminCouponController::class, 'delete'], ['csrf']);

    // Referrals
    $router->get('/referrals', [AdminReferralController::class, 'index']);
    $router->post('/referrals/approve/{id}', [AdminReferralController::class, 'approve'], ['csrf']);
    $router->post('/referrals/reject/{id}', [AdminReferralController::class, 'reject'], ['csrf']);

    // Child Panels
    $router->get('/child-panels', [AdminChildPanelController::class, 'index']);
    $router->post('/child-panels/update/{id}', [AdminChildPanelController::class, 'updateStatus'], ['csrf']);
    $router->post('/child-panels/status/{id}', [AdminChildPanelController::class, 'updateStatus'], ['csrf']);

    // Broadcasts
    $router->get('/broadcasts', [AdminBroadcastController::class, 'index']);
    $router->post('/broadcasts/store', [AdminBroadcastController::class, 'store'], ['csrf']);
    $router->post('/broadcasts/delete/{id}', [AdminBroadcastController::class, 'delete'], ['csrf']);

    // Settings & Themes
    $router->get('/settings', [AdminSettingController::class, 'index']);
    $router->post('/settings', [AdminSettingController::class, 'update'], ['csrf']);
    $router->post('/settings/update', [AdminSettingController::class, 'update'], ['csrf']);

    // Logs
    $router->get('/logs', [AdminLogController::class, 'index']);
});
