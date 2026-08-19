<?php
declare(strict_types=1);

namespace App\Models;

final class Payment extends Model
{
    protected static string $table = 'payments';

    public int $id = 0;
    public int $order_id = 0;
    public int $user_id = 0;
    public string $gateway = 'balance';
    public ?string $transaction_id = null;
    public float $amount = 0.0;
    public string $status = 'pending';
    public ?string $metadata = null;
    public string $created_at = '';

    protected static function allowedFields(): array { return ['order_id', 'user_id', 'gateway', 'transaction_id', 'amount', 'status', 'metadata']; }

    public function order(): ?Order { return Order::find($this->order_id); }
    public function user(): ?User { return User::find($this->user_id); }
}
