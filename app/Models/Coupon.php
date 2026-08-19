<?php
declare(strict_types=1);

namespace App\Models;

final class Coupon extends Model
{
    protected static string $table = 'coupons';

    public int $id = 0;
    public string $code = '';
    public string $type = 'percentage';
    public float $value = 0.0;
    public ?int $max_uses = null;
    public int $used_count = 0;
    public float $min_amount = 0.0;
    public ?int $product_id = null;
    public ?string $expires_at = null;
    public int $active = 1;
    public string $created_at = '';

    protected static function allowedFields(): array { return ['code', 'type', 'value', 'max_uses', 'used_count', 'min_amount', 'product_id', 'expires_at', 'active']; }

    public function isValid(float $cartTotal = 0.0): bool
    {
        if (!$this->active) return false;
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;
        if ($this->expires_at && strtotime($this->expires_at) < time()) return false;
        if ($cartTotal < $this->min_amount) return false;
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'percentage') {
            return round($amount * ($this->value / 100), 2);
        }
        return min($this->value, $amount);
    }

    public static function findByCode(string $code): ?self
    {
        $row = Database::fetchOne('SELECT * FROM coupons WHERE code = ?', [strtoupper($code)]);
        return $row ? self::hydrate($row) : null;
    }
}
