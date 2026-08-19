<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PufferPanel API Client (Auth2 / OAuth2 supported)
 * Supports PufferPanel v3+ API
 */
final class PufferPanelClient
{
    private string $baseUrl;
    private string $clientToken;
    private string $clientSecret;
    private bool $enabled;
    private string $accessToken = '';
    private string $refreshToken = '';

    public function __construct()
    {
        $this->baseUrl = rtrim((string)Settings::get('pufferpanel_url', ''), '/');
        $this->clientToken = (string)Settings::get('pufferpanel_client_token', '');
        $this->clientSecret = (string)Settings::get('pufferpanel_client_secret', '');
        $this->enabled = (bool)Settings::get('pufferpanel_enabled', '0') && $this->clientToken !== '';
    }

    public function isEnabled(): bool { return $this->enabled; }
    public function getTypeLabel(): string { return 'PufferPanel'; }

    /**
     * OAuth2 token exchange using client credentials
     */
    private function authenticate(): bool
    {
        if (!$this->enabled) return false;
        $ch = curl_init($this->baseUrl . '/api/2/auth/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientToken,
                'client_secret' => $this->clientSecret,
            ]),
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status !== 200) return false;
        $data = json_decode((string)$body, true);
        $this->accessToken = $data['access_token'] ?? '';
        $this->refreshToken = $data['refresh_token'] ?? '';
        return $this->accessToken !== '';
    }

    private function request(string $method, string $path, array $data = []): array
    {
        if (!$this->enabled) return $this->simulate($method, $path, $data);
        if (!$this->authenticate()) {
            return ['ok' => false, 'error' => 'PufferPanel OAuth2 authentication failed. Check client token and secret.'];
        }
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
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
        $res = $this->request('GET', '/api/2/nests');
        if (!$res['ok'] || !isset($res['data']['data'])) return [];
        return array_map(fn($n) => ['id' => $n['id'], 'name' => $n['name'] ?? '', 'description' => $n['description'] ?? ''], $res['data']['data']);
    }

    public function listEggs(int $nestId): array
    {
        $res = $this->request('GET', "/api/2/nests/{$nestId}/eggs");
        if (!$res['ok'] || !isset($res['data']['data'])) return [];
        return array_map(fn($e) => ['id' => $e['id'], 'name' => $e['name'] ?? '', 'docker_image' => $e['docker']['image'] ?? '', 'startup' => $e['docker']['command'] ?? '', 'nest' => $nestId], $res['data']['data']);
    }

    public function listNodes(): array
    {
        $res = $this->request('GET', '/api/2/nodes');
        if (!$res['ok'] || !isset($res['data']['data'])) return [];
        return array_map(fn($n) => ['id' => $n['id'], 'name' => $n['name'] ?? ''], $res['data']['data']);
    }

    public function listLocations(): array { return []; }

    public function listAllocations(int $nodeId): array
    {
        $res = $this->request('GET', "/api/2/nodes/{$nodeId}/allocations");
        if (!$res['ok'] || !isset($res['data']['data'])) return [];
        return array_map(fn($a) => ['id' => $a['id'], 'ip' => $a['ip'] ?? '', 'port' => $a['port'] ?? null, 'alias' => $a['alias'] ?? ''], $res['data']['data']);
    }

    public function getEgg(int $eggId, int $nestId): array
    {
        $res = $this->request('GET', "/api/2/nests/{$nestId}/eggs/{$eggId}");
        if (!$res['ok']) return [];
        $e = $res['data'] ?? [];
        return ['id' => $e['id'] ?? $eggId, 'name' => $e['name'] ?? '', 'docker_image' => $e['docker']['image'] ?? '', 'startup' => $e['docker']['command'] ?? '', 'variables' => $e['data'] ?? []];
    }

    public function createServer(array $params): array
    {
        if (!$this->enabled) return $this->simulate('POST', '/api/2/servers', $params);
        $payload = [
            'name' => $params['name'],
            'owner' => (int)($params['pterodactyl_user_id'] ?? 1),
            'egg' => (int)$params['egg_id'],
            'node' => (int)($params['node_id'] ?? 1),
            'install' => ['command' => $params['startup'] ?? ''],
            'run' => ['command' => $params['startup'] ?? ''],
            'docker' => ['image' => $params['docker_image'] ?? ''],
            'data' => $params['variables'] ?? [],
            'limits' => ['cpu' => (int)($params['cpu_percent'] ?? 100), 'memory' => (int)$params['memory_mb'], 'disk' => (int)$params['disk_mb'], 'io' => (int)($params['io_percent'] ?? 500)],
            'allocation' => ['default' => (int)($params['allocation_id'] ?? 0)],
        ];
        $res = $this->request('POST', '/api/2/servers', $payload);
        if (!$res['ok']) return ['ok' => false, 'error' => 'Failed to create PufferPanel server.'];
        $s = $res['data'] ?? [];
        return ['ok' => true, 'server_id' => $s['id'] ?? null, 'identifier' => $s['uuid'] ?? '', 'uuid' => $s['uuid'] ?? ''];
    }

    public function createUser(string $email, string $username, string $password, string $first = 'Nivera', string $last = 'User'): array
    {
        $payload = ['email' => $email, 'username' => $username, 'name' => $first . ' ' . $last, 'password' => $password];
        if (!$this->enabled) return ['ok' => true, 'id' => 1, 'uuid' => 'sim-user'];
        $res = $this->request('POST', '/api/2/users', $payload);
        if (!$res['ok']) return ['ok' => false, 'error' => 'Failed to create PufferPanel user.'];
        $u = $res['data'] ?? [];
        return ['ok' => true, 'id' => $u['id'] ?? null, 'uuid' => $u['uuid'] ?? ''];
    }

    public function findUserByEmail(string $email): ?int
    {
        if (!$this->enabled) return 1;
        $res = $this->request('GET', '/api/2/users');
        if (!$res['ok'] || !isset($res['data']['data'])) return null;
        foreach ($res['data']['data'] as $u) {
            if (($u['email'] ?? '') === $email) return $u['id'] ?? null;
        }
        return null;
    }

    public function suspendServer(int $serverId): array { return $this->request('PATCH', "/api/2/servers/{$serverId}", ['status' => 'suspend']); }
    public function unsuspendServer(int $serverId): array { return $this->request('PATCH', "/api/2/servers/{$serverId}", ['status' => 'install']); }
    public function reinstallServer(int $serverId): array { return $this->request('POST', "/api/2/servers/{$serverId}/reinstall"); }
    public function deleteServer(int $serverId): array { return $this->request('DELETE', "/api/2/servers/{$serverId}"); }

    public function setPowerState(string $identifier, string $action): array
    {
        $map = ['start' => 'start', 'stop' => 'stop', 'restart' => 'restart', 'kill' => 'kill'];
        if (!isset($map[$action])) return ['ok' => false, 'error' => 'Invalid power action.'];
        $res = $this->request('POST', "/api/2/servers/{$identifier}/power/{$map[$action]}");
        return $res['ok'] ? ['ok' => true] : ['ok' => false, 'error' => 'Power action failed.'];
    }

    public function getResources(string $identifier): ?array
    {
        $res = $this->request('GET', "/api/2/servers/{$identifier}");
        if (!$res['ok']) return null;
        $s = $res['data'] ?? [];
        return [
            'current_state' => $s['status'] ?? 'unknown',
            'memory' => ($s['resources']['memory'] ?? 0) * 1048576,
            'cpu' => $s['resources']['cpu'] ?? 0,
            'disk' => ($s['resources']['disk'] ?? 0) * 1048576,
            'memory_limit' => ($s['limits']['memory'] ?? 0),
            'disk_limit' => ($s['limits']['disk'] ?? 0),
            'cpu_limit' => ($s['limits']['cpu'] ?? 100),
            'uptime' => 0,
            'network_rx' => 0,
            'network_tx' => 0,
        ];
    }

    public function sendCommand(string $identifier, string $command): array
    {
        $res = $this->request('POST', "/api/2/servers/{$identifier}/console", ['command' => $command]);
        return $res['ok'] ? ['ok' => true] : ['ok' => false, 'error' => 'Command failed.'];
    }

    public function getServerDetails(string $identifier): ?array
    {
        $res = $this->request('GET', "/api/2/servers/{$identifier}");
        if (!$res['ok']) return null;
        $s = $res['data'] ?? [];
        return ['id' => $s['uuid'] ?? $identifier, 'name' => $s['name'] ?? '', 'state' => $s['status'] ?? 'unknown', 'is_suspended' => false, 'limits' => $s['limits'] ?? [], 'sftp_details' => []];
    }

    public function getWebsocketCredentials(string $identifier): ?array
    {
        $host = str_replace(['https://', 'http://'], '', $this->baseUrl);
        return ['token' => $this->accessToken, 'socket' => 'wss://' . $host . '/api/2/servers/' . $identifier . '/console/ws'];
    }

    private function simulate(string $method, string $path, array $data): array
    {
        if ($method === 'POST' && str_contains($path, '/servers')) return ['ok' => true, 'data' => ['id' => random_int(100,999), 'uuid' => bin2hex(random_bytes(16))]];
        if (str_contains($path, '/power')) return ['ok' => true];
        if (str_contains($path, '/resources') || preg_match('#/servers/[^/]+$#', $path)) {
            return ['ok' => true, 'data' => ['status' => 'running', 'resources' => ['memory' => 256, 'cpu' => 15, 'disk' => 512], 'limits' => ['memory' => 1024, 'disk' => 5120, 'cpu' => 100], 'name' => 'Sim Server']];
        }
        if ($method === 'DELETE') return ['ok' => true];
        if ($method === 'POST' && str_contains($path, '/users')) return ['ok' => true, 'data' => ['id' => 1, 'uuid' => 'sim']];
        if ($method === 'POST' && str_contains($path, '/console')) return ['ok' => true];
        return ['ok' => true, 'data' => []];
    }
}
