<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;
use App\Services\TicketService;

class AdminTicketController
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $tickets = $pdo->query("
            SELECT t.*, u.username, u.email as user_email 
            FROM tickets t 
            INNER JOIN users u ON u.id = t.user_id 
            ORDER BY FIELD(t.status, 'open', 'customer_reply', 'answered', 'closed'), t.id DESC
        ")->fetchAll();

        View::render('admin/tickets', [
            'title' => 'Support Helpdesk',
            'tickets' => $tickets,
        ], 'admin');
    }

    public function show(int $ticketId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT t.*, u.username, u.email as user_email 
            FROM tickets t 
            INNER JOIN users u ON u.id = t.user_id 
            WHERE t.id = :id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $ticketId]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            Response::redirect('/admin/tickets');
        }

        $msgStmt = $pdo->prepare("
            SELECT tm.*, u.username 
            FROM ticket_messages tm 
            INNER JOIN users u ON u.id = tm.user_id 
            WHERE tm.ticket_id = :tid 
            ORDER BY tm.id ASC
        ");
        $msgStmt->execute(['tid' => $ticketId]);

        View::render('admin/ticket_reply', [
            'title' => 'Ticket #' . $ticketId . ' — ' . $ticket['subject'],
            'ticket' => $ticket,
            'messages' => $msgStmt->fetchAll(),
        ], 'admin');
    }

    public function reply(int $ticketId): void
    {
        $request = new Request();
        $message = trim((string)$request->input('message'));
        $status = (string)$request->input('status', 'answered');

        if (empty($message)) {
            Session::setFlash('error', 'Message cannot be empty.');
            Response::redirect('/admin/tickets/' . $ticketId);
        }

        TicketService::reply($ticketId, (int)Auth::id(), $message, true);

        Database::pdo()->prepare("UPDATE tickets SET status = :status, updated_at = NOW() WHERE id = :id")
            ->execute(['status' => $status, 'id' => $ticketId]);

        Security::logAudit("Admin replied to ticket #{$ticketId}");
        Session::setFlash('success', 'Reply posted and ticket status updated.');
        Response::redirect('/admin/tickets/' . $ticketId);
    }

    public function close(int $ticketId): void
    {
        Database::pdo()->prepare("UPDATE tickets SET status = 'closed', updated_at = NOW() WHERE id = :id")
            ->execute(['id' => $ticketId]);

        Security::logAudit("Admin closed ticket #{$ticketId}");
        Session::setFlash('success', "Ticket #{$ticketId} closed.");
        Response::redirect('/admin/tickets');
    }
}
