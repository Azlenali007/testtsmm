<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Security;
use PDO;
use RuntimeException;

class TicketService
{
    public static function createTicket(int $userId, string $subject, string $message, string $priority = 'medium'): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO tickets (user_id, subject, priority, status, created_at) 
                VALUES (:uid, :subject, :priority, 'open', NOW())
            ");
            $stmt->execute([
                'uid' => $userId,
                'subject' => Security::sanitize($subject),
                'priority' => in_array($priority, ['low', 'medium', 'high']) ? $priority : 'medium',
            ]);
            $ticketId = (int)$pdo->lastInsertId();

            $msgStmt = $pdo->prepare("
                INSERT INTO ticket_messages (ticket_id, user_id, is_admin, message, created_at)
                VALUES (:tid, :uid, 0, :msg, NOW())
            ");
            $msgStmt->execute([
                'tid' => $ticketId,
                'uid' => $userId,
                'msg' => Security::sanitize($message),
            ]);

            $pdo->commit();
            return $ticketId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function reply(int $ticketId, int $userId, string $message, bool $isAdmin = false): void
    {
        $pdo = Database::pdo();
        
        $msgStmt = $pdo->prepare("
            INSERT INTO ticket_messages (ticket_id, user_id, is_admin, message, created_at)
            VALUES (:tid, :uid, :admin, :msg, NOW())
        ");
        $msgStmt->execute([
            'tid' => $ticketId,
            'uid' => $userId,
            'admin' => $isAdmin ? 1 : 0,
            'msg' => Security::sanitize($message),
        ]);

        $newStatus = $isAdmin ? 'answered' : 'customer_reply';
        $pdo->prepare("UPDATE tickets SET status = :status, updated_at = NOW() WHERE id = :id")
            ->execute(['status' => $newStatus, 'id' => $ticketId]);
    }
}
