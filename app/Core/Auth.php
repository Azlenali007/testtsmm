<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Auth
{
    private static ?array $cachedUser = null;

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function user(): ?array
    {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $id = self::id();
        if (!$id) {
            return null;
        }

        $stmt = Database::pdo()->prepare("SELECT * FROM users WHERE id = :id AND status != 'suspended' LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        if ($user) {
            self::$cachedUser = $user;
            return self::$cachedUser;
        }

        self::logout();
        return null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        $stmt = Database::pdo()->prepare("
            SELECT r.name FROM roles r
            INNER JOIN user_roles ur ON ur.role_id = r.id
            WHERE ur.user_id = :user_id AND r.name IN ('super_admin', 'admin')
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $user['id']]);
        return (bool)$stmt->fetch();
    }

    public static function isSuperAdmin(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        $stmt = Database::pdo()->prepare("
            SELECT r.name FROM roles r
            INNER JOIN user_roles ur ON ur.role_id = r.id
            WHERE ur.user_id = :user_id AND r.name = 'super_admin'
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $user['id']]);
        return (bool)$stmt->fetch();
    }

    public static function login(array $user): void
    {
        Session::start();
        Session::regenerate();
        Session::set('user_id', (int)$user['id']);
        Session::set('user_name', $user['username']);
        Session::set('user_email', $user['email']);
        self::$cachedUser = $user;
    }

    public static function logout(): void
    {
        self::$cachedUser = null;
        Session::destroy();
    }
}
