<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AdminMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Session::setFlash('error', 'Administrator login required.');
            Response::redirect('/login');
        }

        if (!Auth::isAdmin()) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Unauthorized administrator access (403).'], 403);
            }
            http_response_code(403);
            \App\Core\View::render('errors/403', ['title' => 'Forbidden Access'], 'main');
            exit();
        }
    }
}
