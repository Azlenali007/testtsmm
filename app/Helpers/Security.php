<?php
declare(strict_types=1);

namespace App\Helpers;

class Security
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function sanitize(string $input): string
    {
        return trim(strip_tags($input));
    }

    public static function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public static function generateApiKey(): string
    {
        return 'smm_' . bin2hex(random_bytes(24));
    }

    public static function logAudit(string $action, ?int $userId = null, ?string $details = null): void
    {
        try {
            $stmt = \App\Core\Database::pdo()->prepare("
                INSERT INTO admin_audit_logs (user_id, action, ip_address, user_agent, details, created_at)
                VALUES (:user_id, :action, :ip, :ua, :details, NOW())
            ");
            $stmt->execute([
                'user_id' => $userId,
                'action' => $action,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255),
                'details' => $details,
            ]);
        } catch (\Throwable) {
            // non-blocking
        }
    }
}
