<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Unified Panel Client - Abstracts Pterodactyl, PufferPanel, and Pelican.
 * Routes requests to the correct backend based on panel_type setting.
 */
final class PanelClient
{
    private static ?PanelClient $instance = null;
    private string $type;
    private $driver;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->type = (string)Settings::get('panel_type', 'pterodactyl');
        $this->driver = match($this->type) {
            'pufferpanel' => new PufferPanelClient(),
            'pelican' => new PelicanClient(),
            default => new PterodactylClient(),
        };
    }

    public function getType(): string { return $this->type; }
    public function isEnabled(): bool { return $this->driver->isEnabled(); }
    public function getTypeLabel(): string { return $this->driver->getTypeLabel(); }

    public function listNests(): array { return $this->driver->listNests(); }
    public function listEggs(int $nestId): array { return $this->driver->listEggs($nestId); }
    public function listNodes(): array { return $this->driver->listNodes(); }
    public function listLocations(): array { return $this->driver->listLocations(); }
    public function listAllocations(int $nodeId): array { return $this->driver->listAllocations($nodeId); }
    public function getEgg(int $eggId, int $nestId): array { return $this->driver->getEgg($eggId, $nestId); }
    public function createServer(array $params): array { return $this->driver->createServer($params); }
    public function createUser(string $email, string $username, string $password, string $first = 'Nivera', string $last = 'User'): array { return $this->driver->createUser($email, $username, $password, $first, $last); }
    public function findUserByEmail(string $email): ?int { return $this->driver->findUserByEmail($email); }
    public function suspendServer(int $serverId): array { return $this->driver->suspendServer($serverId); }
    public function unsuspendServer(int $serverId): array { return $this->driver->unsuspendServer($serverId); }
    public function reinstallServer(int $serverId): array { return $this->driver->reinstallServer($serverId); }
    public function deleteServer(int $serverId): array { return $this->driver->deleteServer($serverId); }
    public function setPowerState(string $identifier, string $action): array { return $this->driver->setPowerState($identifier, $action); }
    public function getResources(string $identifier): ?array { return $this->driver->getResources($identifier); }
    public function sendCommand(string $identifier, string $command): array { return $this->driver->sendCommand($identifier, $command); }
    public function getServerDetails(string $identifier): ?array { return $this->driver->getServerDetails($identifier); }
    public function getWebsocketCredentials(string $identifier): ?array { return $this->driver->getWebsocketCredentials($identifier); }
}
