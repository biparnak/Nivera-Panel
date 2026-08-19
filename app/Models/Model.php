<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function find(int $id): ?static
    {
        $row = Database::fetchOne("SELECT * FROM `" . static::$table . "` WHERE `" . static::$primaryKey . "` = ?", [$id]);
        return $row ? static::hydrate($row) : null;
    }

    public static function where(string $condition, array $params = [], string $orderBy = 'id DESC', int $limit = 100): array
    {
        $rows = Database::fetchAll("SELECT * FROM `" . static::$table . "` WHERE {$condition} ORDER BY {$orderBy} LIMIT {$limit}", $params);
        return array_map(fn($r) => static::hydrate($r), $rows);
    }

    public static function all(int $limit = 100): array
    {
        $rows = Database::fetchAll("SELECT * FROM `" . static::$table . "` ORDER BY id DESC LIMIT {$limit}");
        return array_map(fn($r) => static::hydrate($r), $rows);
    }

    public static function count(string $condition = '1=1', array $params = []): int
    {
        return (int)Database::query("SELECT COUNT(*) FROM `" . static::$table . "` WHERE {$condition}", $params)->fetchColumn();
    }

    public static function create(array $data): static
    {
        $keys = array_keys($data);
        $placeholders = implode(', ', array_map(fn($k) => "`{$k}`", $keys));
        $values = implode(', ', array_fill(0, count($keys), '?'));
        Database::query("INSERT INTO `" . static::$table . "` ({$placeholders}) VALUES ({$values})", array_values($data));
        return static::find((int)Database::lastInsertId());
    }

    public function update(array $data): void
    {
        $fields = array_intersect_key($data, array_flip(static::allowedFields()));
        if ($fields === []) return;
        $set = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($fields)));
        Database::query("UPDATE `" . static::$table . "` SET {$set} WHERE `" . static::$primaryKey . "` = ?", [...array_values($fields), $this->{static::$primaryKey}]);
        foreach ($fields as $k => $v) {
            if (property_exists($this, $k)) $this->{$k} = $v;
        }
    }

    public function delete(): void
    {
        Database::query("DELETE FROM `" . static::$table . "` WHERE `" . static::$primaryKey . "` = ?", [$this->{static::$primaryKey}]);
    }

    protected static function allowedFields(): array { return []; }

    protected static function hydrate(array $row): static
    {
        $obj = new static();
        foreach ($row as $key => $value) {
            if (property_exists($obj, $key)) $obj->{$key} = $value;
        }
        return $obj;
    }
}
