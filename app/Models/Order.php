<?php
declare(strict_types=1);

namespace App\Models;

final class Order extends Model
{
    protected static string $table = 'orders';

    public int $id = 0;
    public string $order_number = '';
    public int $user_id = 0;
    public ?int $product_id = null;
    public string $billing_cycle = 'monthly';
    public float $amount = 0.0;
    public string $status = 'pending';
    public ?string $payment_method = null;
    public ?string $paid_at = null;
    public ?string $due_at = null;
    public ?string $next_billing_at = null;
    public string $created_at = '';
    public string $updated_at = '';

    protected static function allowedFields(): array
    {
        return ['order_number', 'user_id', 'product_id', 'billing_cycle', 'amount', 'status', 'payment_method', 'paid_at', 'due_at', 'next_billing_at'];
    }

    public function user(): ?User { return User::find($this->user_id); }
    public function product(): ?Product { return $this->product_id ? Product::find($this->product_id) : null; }
    public function payments(): array { return Payment::where('order_id = ?', [$this->id]); }
    public function server(): ?Server { return Server::where('order_id = ?', [$this->id], 'id DESC', 1)[0] ?? null; }

    public static function generateNumber(): string
    {
        do {
            $number = 'NC-' . strtoupper(bin2hex(random_bytes(4)));
        } while (Database::fetchOne('SELECT 1 FROM orders WHERE order_number = ?', [$number]));
        return $number;
    }

    public static function all(int $limit = 100): array
    {
        return array_map(fn($r) => self::hydrate($r), Database::fetchAll('SELECT * FROM orders ORDER BY id DESC LIMIT ' . $limit));
    }

    public static function revenue(): float
    {
        return (float)(Database::query("SELECT COALESCE(SUM(amount), 0) FROM orders WHERE status = 'paid'")->fetchColumn());
    }

    public static function revenueThisMonth(): float
    {
        return (float)(Database::query("SELECT COALESCE(SUM(amount), 0) FROM orders WHERE status = 'paid' AND MONTH(paid_at) = MONTH(NOW()) AND YEAR(paid_at) = YEAR(NOW())")->fetchColumn());
    }

    public static function countByStatus(string $status): int
    {
        return (int)Database::query("SELECT COUNT(*) FROM orders WHERE status = ?", [$status])->fetchColumn();
    }
}
