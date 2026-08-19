<?php
declare(strict_types=1);

namespace App\Models;

final class TicketMessage extends Model
{
    protected static string $table = 'ticket_messages';

    public int $id = 0;
    public int $ticket_id = 0;
    public int $user_id = 0;
    public string $message = '';
    public int $is_staff = 0;
    public string $created_at = '';

    protected static function allowedFields(): array { return ['ticket_id', 'user_id', 'message', 'is_staff']; }

    public function user(): ?User { return User::find($this->user_id); }
    public function ticket(): ?Ticket { return Ticket::find($this->ticket_id); }
}
