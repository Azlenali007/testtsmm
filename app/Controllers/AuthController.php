<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Helpers\Security;

class AuthController
{
    public function showLogin(): void
    {
        View::render('auth/login', ['title' => 'Sign In to Your Account'], 'auth');
    }

    public function login(): void
    {
        $request = new Request();
        $username = trim((string)$request->input('username'));
        $password = (string)$request->input('password');

        $validator = Validator::make([
            'username' => $username,
            'password' => $password,
        ], [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->first('username') ?? $validator->first('password'));
            Response::redirect('/login');
        }

        $stmt = Database::pdo()->prepare("SELECT * FROM users WHERE (username = :u OR email = :e) LIMIT 1");
        $stmt->execute(['u' => $username, 'e' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::setFlash('error', 'Invalid username or password credentials.');
            Security::logAudit('Failed login attempt for ' . $username, null, 'IP: ' . $request->ip());
            Response::redirect('/login');
        }

        if ($user['status'] === 'suspended') {
            Session::setFlash('error', 'This account has been suspended. Please contact support.');
            Response::redirect('/login');
        }

        Auth::login($user);
        Security::logAudit('User logged in', (int)$user['id']);

        if (Auth::isAdmin()) {
            Response::redirect('/admin');
        }

        Response::redirect('/dashboard');
    }

    public function showRegister(): void
    {
        View::render('auth/register', ['title' => 'Create a Free Account'], 'auth');
    }

    public function register(): void
    {
        $request = new Request();
        $username = trim((string)$request->input('username'));
        $email = trim((string)$request->input('email'));
        $password = (string)$request->input('password');
        $phone = trim((string)$request->input('phone'));
        $refCode = trim((string)$request->input('ref'));

        $validator = Validator::make([
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ], [
            'username' => 'required|min:3|max:32|unique:users,username',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->first('username') ?? $validator->first('email') ?? $validator->first('password'));
            Response::redirect('/register');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $apiKey = Security::generateApiKey();

        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, phone, balance, apikey, status, created_at)
            VALUES (:u, :e, :hash, :p, 0.0000, :api, 'active', NOW())
        ");
        $stmt->execute([
            'u' => $username,
            'e' => $email,
            'hash' => $hash,
            'p' => $phone ?: null,
            'api' => $apiKey,
        ]);

        $userId = (int)$pdo->lastInsertId();

        // Assign default 'user' role
        $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:uid, 4)")
            ->execute(['uid' => $userId]);

        // Process referral code if provided
        if ($refCode) {
            $refStmt = $pdo->prepare("SELECT id FROM users WHERE apikey = :ref_key OR username = :ref_user LIMIT 1");
            $refStmt->execute(['ref_key' => $refCode, 'ref_user' => $refCode]);
            $referrerId = $refStmt->fetchColumn();

            if ($referrerId && (int)$referrerId !== $userId) {
                $pdo->prepare("
                    INSERT INTO referrals (referrer_id, referred_id, commission_rate, status, created_at)
                    VALUES (:ref, :uid, 5.00, 'active', NOW())
                ")->execute(['ref' => $referrerId, 'uid' => $userId]);
            }
        }

        // Auto login
        $user = $pdo->query("SELECT * FROM users WHERE id = {$userId}")->fetch();
        Auth::login($user);

        Security::logAudit('New user registered', $userId);
        Session::setFlash('success', 'Your account has been created successfully! Welcome to ApexSMM.');
        Response::redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/login');
    }

    public function showForgotPassword(): void
    {
        View::render('auth/forgot_password', ['title' => 'Reset Your Password'], 'auth');
    }

    public function forgotPassword(): void
    {
        $email = trim((string)(new Request())->input('email'));
        $stmt = Database::pdo()->prepare("SELECT * FROM users WHERE email = :e LIMIT 1");
        $stmt->execute(['e' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = Security::generateToken(32);
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $up = Database::pdo()->prepare("UPDATE users SET reset_token = :token, reset_token_expires_at = :exp WHERE id = :id");
            $up->execute(['token' => $token, 'exp' => $expires, 'id' => $user['id']]);

            Session::setFlash('success', 'A password reset token has been generated. For testing, your token is: ' . $token);
            Response::redirect('/reset-password?token=' . $token);
        }

        Session::setFlash('error', 'If that email address exists in our database, a recovery link has been prepared.');
        Response::redirect('/forgot-password');
    }

    public function showResetPassword(): void
    {
        $token = (string)(new Request())->get('token');
        View::render('auth/reset_password', ['title' => 'Set New Password', 'token' => $token], 'auth');
    }

    public function resetPassword(): void
    {
        $request = new Request();
        $token = trim((string)$request->input('token'));
        $password = (string)$request->input('password');

        if (strlen($password) < 8) {
            Session::setFlash('error', 'Password must be at least 8 characters long.');
            Response::redirect('/reset-password?token=' . urlencode($token));
        }

        $stmt = Database::pdo()->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_token_expires_at > NOW() LIMIT 1");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            Session::setFlash('error', 'The reset token is invalid or has expired.');
            Response::redirect('/forgot-password');
        }

        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $up = Database::pdo()->prepare("UPDATE users SET password_hash = :hash, reset_token = NULL, reset_token_expires_at = NULL WHERE id = :id");
        $up->execute(['hash' => $newHash, 'id' => $user['id']]);

        Session::setFlash('success', 'Your password has been reset successfully. Please sign in with your new password.');
        Response::redirect('/login');
    }
}
