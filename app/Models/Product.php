<?php
declare(strict_types=1);

namespace App\Models;

final class Product extends Model
{
    protected static string $table = 'products';

    public int $id = 0;
    public ?int $category_id = null;
    public string $name = '';
    public ?string $slug = null;
    public ?string $description = null;
    public string $icon = '🖥';
    public float $price_monthly = 0.0;
    public float $price_yearly = 0.0;
    public float $setup_fee = 0.0;
    public int $memory_mb = 1024;
    public int $disk_mb = 5120;
    public int $cpu_percent = 100;
    public int $swap_mb = 512;
    public int $io_percent = 500;
    public int $databases = 1;
    public int $backups = 1;
    public int $allocations = 1;
    public int $slots = 0;
    public ?int $node_id = null;
    public ?int $egg_id = null;
    public ?int $nest_id = null;
    public ?string $egg_name = null;
    public ?string $docker_image = null;
    public ?string $startup = null;
    public ?string $default_variables = null;
    public int $sort_order = 0;
    public int $featured = 0;
    public int $active = 1;
    public string $created_at = '';
    public string $updated_at = '';

    protected static function allowedFields(): array
    {
        return ['category_id', 'name', 'slug', 'description', 'icon', 'price_monthly', 'price_yearly', 'setup_fee', 'memory_mb', 'disk_mb', 'cpu_percent', 'swap_mb', 'io_percent', 'databases', 'backups', 'allocations', 'slots', 'node_id', 'egg_id', 'nest_id', 'egg_name', 'docker_image', 'startup', 'default_variables', 'sort_order', 'featured', 'active'];
    }

    public function category(): ?Category { return $this->category_id ? Category::find($this->category_id) : null; }

    public function price(string $cycle = 'monthly'): float
    {
        return $cycle === 'yearly' ? $this->price_yearly : $this->price_monthly;
    }

    public function getVariables(): array
    {
        return $this->default_variables ? json_decode($this->default_variables, true) : [];
    }

    public function isActive(): bool { return (bool)$this->active; }

    public static function findBySlug(string $slug): ?self
    {
        $row = Database::fetchOne('SELECT * FROM products WHERE slug = ?', [$slug]);
        return $row ? self::hydrate($row) : null;
    }

    public static function allActive(int $limit = 100): array
    {
        return array_map(fn($r) => self::hydrate($r), Database::fetchAll('SELECT * FROM products WHERE active = 1 ORDER BY sort_order ASC, id ASC LIMIT ' . $limit));
    }

    public static function all(string $search = '', int $limit = 100): array
    {
        if ($search !== '') {
            $rows = Database::fetchAll('SELECT * FROM products WHERE name LIKE ? ORDER BY sort_order ASC, id ASC LIMIT ' . $limit, ["%{$search}%"]);
        } else {
            $rows = Database::fetchAll('SELECT * FROM products ORDER BY sort_order ASC, id ASC LIMIT ' . $limit);
        }
        return array_map(fn($r) => self::hydrate($r), $rows);
    }

    public static function slugify(string $text): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
        return $slug ?: 'product';
    }
}
