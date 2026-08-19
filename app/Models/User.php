<?php
declare(strict_types=1);

namespace App\Models;

final class User extends Model
{
    protected static string $table = 'users';

    public int $id = 0;
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'user';
    public ?string $avatar = null;
    public string $balance = '0.00';
    public string $status = 'active';
    public ?int $pterodactyl_user_id = null;
    public ?int $pufferpanel_user_id = null;
    public ?int $pelican_user_id = null;
    public ?string $remember_token = null;
    public ?string $last_login_at = null;
    public ?string $last_login_ip = null;
    public string $created_at = '';
    public string $updated_at = '';

    public static function findByEmail(string $email): ?self
    {
        $row = Database::fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
        return $row ? self::hydrate($row) : null;
    }

    public static function findByUsername(string $username): ?self
    {
        $row = Database::fetchOne('SELECT * FROM users WHERE username = ?', [$username]);
        return $row ? self::hydrate($row) : null;
    }

    public static function all(string $search = '', int $limit = 100): array
    {
        if ($search !== '') {
            $rows = Database::fetchAll(
                'SELECT * FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT ' . $limit,
                ["%{$search}%", "%{$search}%"]
            );
        } else {
            $rows = Database::fetchAll('SELECT * FROM users ORDER BY created_at DESC LIMIT ' . $limit);
        }
        return array_map(fn($r) => self::hydrate($r), $rows);
    }

    protected static function allowedFields(): array
    {
        return ['username', 'email', 'password', 'role', 'avatar', 'balance', 'status', 'remember_token', 'last_login_at', 'last_login_ip', 'pterodactyl_user_id', 'pufferpanel_user_id', 'pelican_user_id'];
    }

    public function servers(): array { return Server::where('user_id = ?', [$this->id]); }
    public function orders(): array { return Order::where('user_id = ? ORDER BY id DESC', [$this->id]); }
    public function tickets(): array { return Ticket::where('user_id = ? ORDER BY id DESC', [$this->id]); }

    public function verifyPassword(string $password): bool { return password_verify($password, $this->password); }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isSuspended(): bool { return $this->status === 'suspended'; }
    public function isBanned(): bool { return $this->status === 'banned'; }
    public function getBalance(): float { return (float)$this->balance; }

    public function addFunds(float $amount): void
    {
        $this->update(['balance' => (string)($this->getBalance() + $amount)]);
    }

    public function deductFunds(float $amount): bool
    {
        if ($this->getBalance() < $amount) return false;
        $this->update(['balance' => (string)($this->getBalance() - $amount)]);
        return true;
    }

    /**
     * Get or create user on the active hosting panel.
     * Returns the panel user ID.
     */
    public function getOrCreatePanelUser(): ?int
    {
        $panel = PanelClient::instance();
        if (!$panel->isEnabled()) return null;

        $type = $panel->getType();
        $field = match($type) { 'pufferpanel' => 'pufferpanel_user_id', 'pelican' => 'pelican_user_id', default => 'pterodactyl_user_id' };

        if ($this->{$field}) return (int)$this->{$field};

        $existing = $panel->findUserByEmail($this->email);
        if ($existing) {
            $this->update([$field => $existing]);
            return $existing;
        }

        $pass = bin2hex(random_bytes(16));
        $created = $panel->createUser($this->email, $this->username, $pass);
        if ($created['ok'] && isset($created['id'])) {
            $this->update([$field => $created['id']]);
            return $created['id'];
        }
        return null;
    }
}
