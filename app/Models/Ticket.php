<?php
declare(strict_types=1);

namespace App\Models;

final class Ticket extends Model
{
    protected static string $table = 'tickets';

    public int $id = 0;
    public int $user_id = 0;
    public ?int $server_id = null;
    public string $ticket_number = '';
    public string $subject = '';
    public string $status = 'open';
    public string $priority = 'medium';
    public string $department = 'general';
    public string $created_at = '';
    public string $updated_at = '';

    protected static function allowedFields(): array { return ['user_id', 'server_id', 'ticket_number', 'subject', 'status', 'priority', 'department']; }

    public function user(): ?User { return User::find($this->user_id); }
    public function server(): ?Server { return $this->server_id ? Server::find($this->server_id) : null; }
    public function messages(): array { return TicketMessage::where('ticket_id = ?', [$this->id], 'created_at ASC', 500); }

    public function lastMessage(): ?TicketMessage
    {
        $msgs = TicketMessage::where('ticket_id = ?', [$this->id], 'created_at DESC', 1);
        return $msgs[0] ?? null;
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'TK-' . strtoupper(bin2hex(random_bytes(3)));
        } while (Database::fetchOne('SELECT 1 FROM tickets WHERE ticket_number = ?', [$number]));
        return $number;
    }

    public static function all(int $limit = 100, string $status = ''): array
    {
        $where = $status !== '' ? 'WHERE status = ?' : 'WHERE 1=1';
        $params = $status !== '' ? [$status] : [];
        return array_map(fn($r) => self::hydrate($r), Database::fetchAll("SELECT * FROM tickets {$where} ORDER BY id DESC LIMIT {$limit}", $params));
    }
}
