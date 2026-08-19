<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Pelican Panel API Client
 * Pelican is a fork of Pterodactyl with extended features.
 * Uses same API structure but with additional endpoints.
 */
final class PelicanClient
{
    private string $baseUrl;
    private string $apiKey;
    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl = rtrim((string)Settings::get('pelican_url', Settings::get('pterodactyl_url', '')), '/');
        $this->apiKey = (string)Settings::get('pelican_api_key', Settings::get('pterodactyl_api_key', ''));
        $this->enabled = (bool)Settings::get('pelican_enabled', '0') && $this->apiKey !== '';
    }

    public function isEnabled(): bool { return $this->enabled; }
    public function getTypeLabel(): string { return 'Pelican'; }

    private function request(string $method, string $path, array $data = [], string $apiType = 'application'): array
    {
        if (!$this->enabled) return $this->simulate($method, $path, $data);
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        $headers = [
            'Accept: application/vnd.pterodactyl.v1+json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST => $method,
        ];
        if (in_array($method, ['POST', 'PATCH', 'PUT'], true) && $data !== []) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($body === false) return ['ok' => false, 'error' => 'cURL error: ' . $error];
        $decoded = json_decode($body, true);
        return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'data' => $decoded];
    }

    public function listNests(): array
    {
        $res = $this->request('GET', '/api/application/nests?per_page=100');
        if (!$res['ok'] || !isset($res['data']['data'])) return [];
        return array_map(fn($item) => ['id' => $item['attributes']['id'], 'name' => $item['attributes']['name'] ?? '', 'description' => $item['attributes']['description'] ?? ''], $res['data']['data']);
    }

    public function listEggs(int $nestId): array
    {
        $res = $this->request('GET', "/api/application/nests/{$nestId}/eggs?include=servers&per_page=100");
        if (!$res['ok'] || !isset($res['data']['data'])) return [];
        return array_map(fn($item) => ['id' => $item['attributes']['id'], 'name' => $item['attributes']['name'] ?? '', 'docker_image' => $item['attributes']['docker_image'] ?? '', 'startup' => $item['attributes']['startup'] ?? '', 'nest' => $nestId], $res['data']['data']);
    }

    public function listNodes(): array
    {
        $res = $this->request('GET', '/api/application/nodes?per_page=100');
        if (!$res['ok'] || !isset($res['data']['data'])) return [];
        return array_map(fn($item) => ['id' => $item['attributes']['id'], 'name' => $item['attributes']['name'] ?? ''], $res['data']['data']);
    }

    public function listLocations(): array
    {
        $res = $this->request('GET', '/api/application/locations?per_page=100');
        if (!$res['ok'] || !isset($res['data']['data'])) return [];
        return array_map(fn($item) => ['id' => $item['attributes']['id'], 'short' => $item['attributes']['short'] ?? '', 'long' => $item['attributes']['long'] ?? ''], $res['data']['data']);
    }

    public function listAllocations(int $nodeId): array
    {
        $res = $this->request('GET', "/api/application/nodes/{$nodeId}/allocations?per_page=100");
        if (!$res['ok'] || !isset($res['data']['data'])) return [];
        $allocs = [];
        foreach ($res['data']['data'] as $item) {
            $a = $item['attributes'] ?? $item;
            if (empty($a['assigned'])) $allocs[] = ['id' => $a['id'], 'ip' => $a['ip'], 'port' => $a['port'] ?? null, 'alias' => $a['alias'] ?? ''];
        }
        return $allocs;
    }

    public function getEgg(int $eggId, int $nestId): array
    {
        $res = $this->request('GET', "/api/application/nests/{$nestId}/eggs/{$eggId}?include=variables");
        if (!$res['ok']) return [];
        $attr = $res['data']['attributes'] ?? $res['data'];
        $variables = [];
        if (isset($attr['relationships']['variables']['data'])) {
            foreach ($attr['relationships']['variables']['data'] as $v) {
                $va = $v['attributes'] ?? $v;
                $variables[] = ['name' => $va['name'] ?? '', 'env_variable' => $va['env_variable'] ?? '', 'default_value' => $va['default_value'] ?? '', 'description' => $va['description'] ?? ''];
            }
        }
        return ['id' => $attr['id'] ?? $eggId, 'name' => $attr['name'] ?? '', 'docker_image' => $attr['docker_image'] ?? '', 'startup' => $attr['startup'] ?? '', 'variables' => $variables];
    }

    public function createServer(array $params): array
    {
        if (!$this->enabled) return $this->simulate('POST', '/api/application/servers', $params);
        $payload = [
            'name' => $params['name'],
            'user' => $params['pterodactyl_user_id'],
            'egg' => (int)$params['egg_id'],
            'docker_image' => $params['docker_image'] ?? '',
            'startup' => $params['startup'] ?? '',
            'environment' => $params['variables'] ?? [],
            'limits' => ['memory' => (int)$params['memory_mb'], 'swap' => (int)($params['swap_mb'] ?? 0), 'disk' => (int)$params['disk_mb'], 'io' => (int)($params['io_percent'] ?? 500), 'cpu' => (int)($params['cpu_percent'] ?? 100)],
            'feature_limits' => ['databases' => (int)($params['databases'] ?? 0), 'backups' => (int)($params['backups'] ?? 0), 'allocations' => (int)($params['allocations'] ?? 0)],
            'allocation' => ['default' => (int)$params['allocation_id']],
        ];
        $res = $this->request('POST', '/api/application/servers', $payload);
        if (!$res['ok']) return ['ok' => false, 'error' => 'Failed to create server on Pelican.'];
        $attr = $res['data']['attributes'] ?? $res['data'];
        return ['ok' => true, 'server_id' => $attr['id'] ?? null, 'identifier' => $attr['identifier'] ?? '', 'uuid' => $attr['uuid'] ?? ''];
    }

    public function createUser(string $email, string $username, string $password, string $first = 'Nivera', string $last = 'User'): array
    {
        $payload = ['email' => $email, 'username' => $username, 'first_name' => $first, 'last_name' => $last, 'password' => $password];
        if (!$this->enabled) return ['ok' => true, 'id' => 1, 'uuid' => 'sim-user'];
        $res = $this->request('POST', '/api/application/users', $payload);
        if (!$res['ok']) return ['ok' => false, 'error' => 'Failed to create Pelican user.'];
        $attr = $res['data']['attributes'] ?? $res['data'];
        return ['ok' => true, 'id' => $attr['id'] ?? null, 'uuid' => $attr['uuid'] ?? ''];
    }

    public function findUserByEmail(string $email): ?int
    {
        if (!$this->enabled) return 1;
        $res = $this->request('GET', '/api/application/users?filter[email]=' . urlencode($email));
        if (!$res['ok'] || !isset($res['data']['data'][0])) return null;
        return $res['data']['data'][0]['attributes']['id'] ?? null;
    }

    public function suspendServer(int $serverId): array { return $this->request('POST', "/api/application/servers/{$serverId}/suspend"); }
    public function unsuspendServer(int $serverId): array { return $this->request('POST', "/api/application/servers/{$serverId}/unsuspend"); }
    public function reinstallServer(int $serverId): array { return $this->request('POST', "/api/application/servers/{$serverId}/reinstall"); }
    public function deleteServer(int $serverId): array { return $this->request('DELETE', "/api/application/servers/{$serverId}"); }

    public function setPowerState(string $identifier, string $action): array
    {
        if (!in_array($action, ['start', 'stop', 'restart', 'kill'], true)) return ['ok' => false, 'error' => 'Invalid power action.'];
        $res = $this->request('POST', "/api/client/servers/{$identifier}/power", ['signal' => $action], 'client');
        return $res['ok'] ? ['ok' => true] : ['ok' => false, 'error' => 'Power action failed.'];
    }

    public function getResources(string $identifier): ?array
    {
        $res = $this->request('GET', "/api/client/servers/{$identifier}/resources", [], 'client');
        if (!$res['ok']) return null;
        $attr = $res['data']['attributes'] ?? $res['data'];
        return [
            'current_state' => $attr['current_state'] ?? 'unknown',
            'memory' => $attr['resources']['memory_bytes'] ?? 0,
            'cpu' => $attr['resources']['cpu_absolute'] ?? 0,
            'disk' => $attr['resources']['disk_bytes'] ?? 0,
            'memory_limit' => $attr['limits']['memory'] ?? 0,
            'disk_limit' => $attr['limits']['disk'] ?? 0,
            'cpu_limit' => $attr['limits']['cpu'] ?? 0,
            'uptime' => $attr['resources']['uptime'] ?? 0,
            'network_rx' => $attr['resources']['network_rx_bytes'] ?? 0,
            'network_tx' => $attr['resources']['network_tx_bytes'] ?? 0,
        ];
    }

    public function sendCommand(string $identifier, string $command): array
    {
        $res = $this->request('POST', "/api/client/servers/{$identifier}/command", ['command' => $command], 'client');
        return $res['ok'] ? ['ok' => true] : ['ok' => false, 'error' => 'Command failed.'];
    }

    public function getServerDetails(string $identifier): ?array
    {
        $res = $this->request('GET', "/api/client/servers/{$identifier}", [], 'client');
        if (!$res['ok']) return null;
        $a = $res['data']['attributes'] ?? $res['data'];
        return ['id' => $a['identifier'], 'name' => $a['name'] ?? '', 'state' => $a['status'] ?? 'unknown', 'is_suspended' => $a['is_suspended'] ?? false, 'limits' => $a['limits'] ?? [], 'sftp_details' => $a['sftp_details'] ?? []];
    }

    public function getWebsocketCredentials(string $identifier): ?array
    {
        $res = $this->request('GET', "/api/client/servers/{$identifier}/websocket", [], 'client');
        if (!$res['ok'] || !isset($res['data'])) return null;
        return ['token' => $res['data']['data']['token'] ?? '', 'socket' => $res['data']['data']['socket'] ?? ''];
    }

    private function simulate(string $method, string $path, array $data): array
    {
        if ($method === 'POST' && str_contains($path, '/servers')) return ['ok' => true, 'data' => ['attributes' => ['id' => random_int(100,999), 'identifier' => bin2hex(random_bytes(4)), 'uuid' => bin2hex(random_bytes(16))]]];
        if (str_contains($path, '/power')) return ['ok' => true];
        if (str_contains($path, '/command')) return ['ok' => true];
        if (str_contains($path, '/resources')) return ['ok' => true, 'data' => ['attributes' => ['current_state' => 'running', 'resources' => ['memory_bytes' => 256*1048576, 'cpu_absolute' => 15, 'disk_bytes' => 512*1048576, 'network_rx_bytes' => 1000000, 'network_tx_bytes' => 1000000, 'uptime' => 3600], 'limits' => ['memory' => 1024, 'disk' => 5120, 'cpu' => 100]]]];
        if (str_contains($path, '/websocket')) return ['ok' => true, 'data' => ['data' => ['token' => 'pel-' . bin2hex(random_bytes(8)), 'socket' => 'wss://panel.example.com:8443/socket']]];
        if ($method === 'POST' && str_contains($path, '/users')) return ['ok' => true, 'data' => ['attributes' => ['id' => 1, 'uuid' => 'sim']]];
        if ($method === 'DELETE') return ['ok' => true];
        if ($method === 'POST' && str_contains($path, '/suspend')) return ['ok' => true];
        if ($method === 'POST' && str_contains($path, '/unsuspend')) return ['ok' => true];
        return ['ok' => true, 'data' => []];
    }
}
