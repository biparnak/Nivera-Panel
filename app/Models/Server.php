<?php
declare(strict_types=1);

namespace App\Models;

final class Server extends Model
{
    protected static string $table = 'servers';

    public int $id = 0;
    public int $user_id = 0;
    public ?int $product_id = null;
    public ?int $order_id = null;
    public string $name = '';
    public ?int $external_id = null;
    public ?string $external_identifier = null;
    public ?string $external_uuid = null;
    public ?int $node_id = null;
    public ?int $egg_id = null;
    public int $memory_mb = 1024;
    public int $disk_mb = 5120;
    public int $cpu_percent = 100;
    public int $swap_mb = 512;
    public int $io_percent = 500;
    public int $databases = 1;
    public int $backups = 1;
    public int $allocations = 1;
    public string $state = 'pending';
    public string $panel_type = 'pterodactyl';
    public ?string $expires_at = null;
    public ?string $suspended_at = null;
    public string $created_at = '';
    public string $updated_at = '';

    protected static function allowedFields(): array
    {
        return ['user_id', 'product_id', 'order_id', 'name', 'external_id', 'external_identifier', 'external_uuid', 'node_id', 'egg_id', 'memory_mb', 'disk_mb', 'cpu_percent', 'swap_mb', 'io_percent', 'databases', 'backups', 'allocations', 'state', 'panel_type', 'expires_at', 'suspended_at'];
    }

    public function user(): ?User { return User::find($this->user_id); }
    public function product(): ?Product { return $this->product_id ? Product::find($this->product_id) : null; }
    public function order(): ?Order { return $this->order_id ? Order::find($this->order_id) : null; }

    public function isOwner(int $userId): bool { return $this->user_id === $userId; }

    public function isExpired(): bool
    {
        if (!$this->expires_at) return false;
        return strtotime($this->expires_at) < time();
    }

    /**
     * Get the PanelClient configured for this server's panel type
     */
    public function getPanelClient(): \App\Core\PanelClient
    {
        return \App\Core\PanelClient::instance();
    }

    public function getIdentifier(): string
    {
        return $this->external_identifier ?? (string)$this->external_id ?? '';
    }
}
