<?php
declare(strict_types=1);

namespace App\Models;

final class Category extends Model
{
    protected static string $table = 'categories';

    public int $id = 0;
    public string $name = '';
    public string $slug = '';
    public ?string $description = null;
    public ?string $icon = null;
    public int $sort_order = 0;
    public int $active = 1;
    public string $created_at = '';

    protected static function allowedFields(): array { return ['name', 'slug', 'description', 'icon', 'sort_order', 'active']; }

    public function products(): array { return Product::where('category_id = ? AND active = 1', [$this->id], 'sort_order ASC, id ASC'); }

    public static function allActive(): array
    {
        return array_map(fn($r) => self::hydrate($r), Database::fetchAll('SELECT * FROM categories WHERE active = 1 ORDER BY sort_order ASC'));
    }
}
