<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        // Only validate state-changing methods
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'], true)) {
            // Exclude public external API and payment webhooks which use cryptographic signatures or API keys
            $uri = $request->getUri();
            if (str_starts_with($uri, '/api/') || str_starts_with($uri, '/payment/webhook/')) {
                return;
            }

            $token = $request->input('_csrf_token') ?? 
                     $request->input('csrf_token') ?? 
                     $request->all()['_csrf_token'] ?? 
                     $request->all()['csrf_token'] ?? 
                     ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

            if (!Csrf::validate($token)) {
                if ($request->isAjax()) {
                    Response::json(['success' => false, 'message' => 'CSRF verification failed. Please refresh the page and try again.'], 419);
                }
                Session::setFlash('error', 'Your session has expired. Please submit the form again.');
                Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
        }
    }
}
