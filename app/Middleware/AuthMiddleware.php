<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Authentication required.'], 401);
            }
            Session::setFlash('error', 'Please log in to continue.');
            Response::redirect('/login');
        }
    }
}
