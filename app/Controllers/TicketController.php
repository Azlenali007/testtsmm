<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\TicketService;

class TicketController
{
    public function index(): void
    {
        $user = Auth::user();
        $pdo = Database::pdo();

        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = :uid ORDER BY id DESC");
        $stmt->execute(['uid' => $user['id']]);
        $tickets = $stmt->fetchAll();

        View::render('user/tickets', [
            'title' => 'Support Tickets',
            'tickets' => $tickets,
        ], 'main');
    }

    public function create(): void
    {
        View::render('user/ticket_create', [
            'title' => 'Open a Support Ticket',
        ], 'main');
    }

    public function store(): void
    {
        $request = new Request();
        $user = Auth::user();

        $subject = trim((string)$request->input('subject'));
        $message = trim((string)$request->input('message'));
        $priority = (string)$request->input('priority', 'medium');

        if (empty($subject) || empty($message)) {
            Session::setFlash('error', 'Please provide both a ticket subject and description.');
            Response::redirect('/tickets/create');
        }

        try {
            $ticketId = TicketService::createTicket((int)$user['id'], $subject, $message, $priority);
            Session::setFlash('success', "Ticket #{$ticketId} created successfully. Our team will review it shortly.");
            Response::redirect('/tickets/' . $ticketId);
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            Response::redirect('/tickets/create');
        }
    }

    public function show(int $ticketId): void
    {
        $user = Auth::user();
        $pdo = Database::pdo();

        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute(['id' => $ticketId, 'uid' => $user['id']]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            Response::redirect('/tickets');
        }

        $msgStmt = $pdo->prepare("
            SELECT tm.*, u.username 
            FROM ticket_messages tm 
            INNER JOIN users u ON u.id = tm.user_id 
            WHERE tm.ticket_id = :tid 
            ORDER BY tm.id ASC
        ");
        $msgStmt->execute(['tid' => $ticketId]);
        $messages = $msgStmt->fetchAll();

        View::render('user/ticket_view', [
            'title' => 'Ticket #' . $ticketId . ' — ' . $ticket['subject'],
            'ticket' => $ticket,
            'messages' => $messages,
        ], 'main');
    }

    public function reply(int $ticketId): void
    {
        $request = new Request();
        $user = Auth::user();
        $message = trim((string)$request->input('message'));

        if (empty($message)) {
            Session::setFlash('error', 'Reply message cannot be empty.');
            Response::redirect('/tickets/' . $ticketId);
        }

        $stmt = Database::pdo()->prepare("SELECT id, status FROM tickets WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute(['id' => $ticketId, 'uid' => $user['id']]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            Response::redirect('/tickets');
        }

        if ($ticket['status'] === 'closed') {
            Session::setFlash('error', 'This ticket is closed. Please open a new ticket for further inquiries.');
            Response::redirect('/tickets/' . $ticketId);
        }

        TicketService::reply($ticketId, (int)$user['id'], $message, false);

        Session::setFlash('success', 'Your reply has been posted.');
        Response::redirect('/tickets/' . $ticketId);
    }
}
