<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class GuestMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            if (Auth::isAdmin()) {
                Response::redirect('/admin');
            }
            Response::redirect('/dashboard');
        }
    }
}
