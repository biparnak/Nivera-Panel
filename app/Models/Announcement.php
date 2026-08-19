<?php
declare(strict_types=1);

namespace App\Models;

final class Announcement extends Model
{
    protected static string $table = 'announcements';

    public int $id = 0;
    public string $title = '';
    public string $content = '';
    public string $type = 'info';
    public int $pinned = 0;
    public ?int $author_id = null;
    public string $created_at = '';

    protected static function allowedFields(): array { return ['title', 'content', 'type', 'pinned', 'author_id']; }

    public function author(): ?User { return $this->author_id ? User::find($this->author_id) : null; }

    public static function allActive(int $limit = 10): array
    {
        return array_map(fn($r) => self::hydrate($r), Database::fetchAll('SELECT * FROM announcements ORDER BY pinned DESC, id DESC LIMIT ' . $limit));
    }
}
