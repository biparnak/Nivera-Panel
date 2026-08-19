<?php
declare(strict_types=1);

namespace App\Core;

final class Settings
{
    private static array $cache = [];
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) return;
        if (!self::tableExists()) {
            self::$loaded = true;
            return;
        }
        $rows = Database::fetchAll('SELECT `key`, `value` FROM settings');
        foreach ($rows as $row) {
            self::$cache[$row['key']] = $row['value'];
        }
        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::$cache[$key] = $value;
    }

    public static function all(): array { return self::$cache; }

    public static function update(string $key, mixed $value): void
    {
        self::set($key, $value);
        Database::query(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, (string)$value]
        );
    }

    public static function massUpdate(array $values): void
    {
        foreach ($values as $key => $value) {
            self::update($key, $value);
        }
    }

    private static function tableExists(): bool
    {
        try {
            Database::query('SELECT 1 FROM settings LIMIT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
