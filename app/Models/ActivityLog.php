<?php
declare(strict_types=1);

namespace App\Models;

final class ActivityLog extends Model
{
    protected static string $table = 'activity_log';

    public int $id = 0;
    public ?int $user_id = null;
    public string $action = '';
    public ?string $description = null;
    public ?string $ip = null;
    public string $created_at = '';

    protected static function allowedFields(): array { return ['user_id', 'action', 'description', 'ip']; }

    public static function log(string $action, ?string $description = null, ?int $userId = null): void
    {
        self::create([
            'user_id' => $userId ?? (\App\Core\Auth::id()),
            'action' => $action,
            'description' => $description,
            'ip' => \App\Core\Request::ip(),
        ]);
    }

    public static function recent(int $limit = 50): array
    {
        return array_map(fn($r) => self::hydrate($r), Database::fetchAll('SELECT a.*, u.username FROM activity_log a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT ' . $limit));
    }
}
