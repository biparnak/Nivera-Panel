<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Models\Ticket;
use App\Models\TicketMessage;

final class SupportController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('support/index', ['tickets' => Auth::user()->tickets()]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('support/create');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $subject = trim((string)($_POST['subject'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));
        $department = $_POST['department'] ?? 'general';
        $serverId = $_POST['server_id'] ?: null;

        if ($subject === '' || $message === '') {
            Session::flash('error', 'Subject and message are required.');
            $this->redirect('/support/create');
        }

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'server_id' => $serverId ? (int)$serverId : null,
            'ticket_number' => Ticket::generateNumber(),
            'subject' => $subject,
            'department' => $department,
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $message,
        ]);

        Session::flash('success', 'Ticket #' . $ticket->ticket_number . ' created.');
        $this->redirect('/support/' . $ticket->id);
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $ticket = Ticket::find((int)$id);
        if (!$ticket || ($ticket->user_id !== Auth::id() && !Auth::isAdmin())) {
            \App\Core\View::renderError('Ticket not found.', 404);
            return;
        }
        $this->view('support/show', ['ticket' => $ticket, 'messages' => $ticket->messages()]);
    }

    public function reply(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $ticket = Ticket::find((int)$id);
        if (!$ticket || ($ticket->user_id !== Auth::id() && !Auth::isAdmin())) {
            Session::flash('error', 'Ticket not found.');
            $this->redirect('/support');
        }
        $message = trim((string)($_POST['message'] ?? ''));
        if ($message === '') {
            Session::flash('error', 'Message cannot be empty.');
            $this->redirect('/support/' . $id);
        }
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $message,
            'is_staff' => Auth::isAdmin() ? 1 : 0,
        ]);
        if ($ticket->status === 'open' && Auth::isAdmin()) {
            $ticket->update(['status' => 'replied']);
        }
        Session::flash('success', 'Reply sent.');
        $this->redirect('/support/' . $id);
    }

    public function close(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $ticket = Ticket::find((int)$id);
        if ($ticket && ($ticket->user_id === Auth::id() || Auth::isAdmin())) {
            $ticket->update(['status' => 'closed']);
            Session::flash('success', 'Ticket closed.');
        }
        $this->redirect('/support');
    }
}
