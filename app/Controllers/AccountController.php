<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;

class AccountController
{
    public function index(): void
    {
        $user = Auth::user();
        View::render('user/account', [
            'title' => 'Account Settings & API Key',
            'user' => $user,
        ], 'main');
    }

    public function updatePassword(): void
    {
        $request = new Request();
        $user = Auth::user();

        $current = (string)$request->input('current_password');
        $newPass = (string)$request->input('new_password');
        $confirm = (string)$request->input('confirm_password');

        if (!password_verify($current, $user['password_hash'])) {
            Session::setFlash('error', 'Current password entered is incorrect.');
            Response::redirect('/account');
        }

        if (strlen($newPass) < 8) {
            Session::setFlash('error', 'New password must be at least 8 characters long.');
            Response::redirect('/account');
        }

        if ($newPass !== $confirm) {
            Session::setFlash('error', 'New password and confirmation do not match.');
            Response::redirect('/account');
        }

        $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $up = Database::pdo()->prepare("UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id");
        $up->execute(['hash' => $newHash, 'id' => $user['id']]);

        Security::logAudit('User updated password', (int)$user['id']);
        Session::setFlash('success', 'Your password has been changed successfully.');
        Response::redirect('/account');
    }

    public function updateTimezone(): void
    {
        $request = new Request();
        $user = Auth::user();
        $tz = trim((string)$request->input('timezone', 'UTC'));

        $stmt = Database::pdo()->prepare("UPDATE users SET timezone = :tz, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['tz' => $tz, 'id' => $user['id']]);

        Session::setFlash('success', 'Timezone preference updated.');
        Response::redirect('/account');
    }

    public function regenerateApiKey(): void
    {
        $user = Auth::user();
        $newKey = Security::generateApiKey();

        $up = Database::pdo()->prepare("UPDATE users SET apikey = :key, updated_at = NOW() WHERE id = :id");
        $up->execute(['key' => $newKey, 'id' => $user['id']]);

        Security::logAudit('Regenerated SMM API key', (int)$user['id']);
        Session::setFlash('success', 'New API Key generated successfully! Update your client applications.');
        Response::redirect('/account');
    }

    public function apiDocs(): void
    {
        $user = Auth::user();
        View::render('user/api_docs', [
            'title' => 'SMM v2 API Documentation',
            'user' => $user,
            'apiUrl' => (require __DIR__ . '/../../config/app.php')['url'] . '/api/v2',
        ], 'main');
    }
}
