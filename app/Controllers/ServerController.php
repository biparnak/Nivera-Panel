<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\PanelClient;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Server;

final class ServerController extends Controller
{
    private function findOwned(int $id): Server
    {
        $this->requireAuth();
        $server = Server::find($id);
        if (!$server || !$server->isOwner(Auth::id())) {
            \App\Core\View::renderError('Server not found.', 404);
            exit;
        }
        return $server;
    }

    public function index(): void { $this->redirect('/dashboard'); }

    public function show(string $id): void
    {
        $server = $this->findOwned((int)$id);
        $panel = PanelClient::instance();
        $resources = null;
        $state = $server->state;
        if ($server->external_identifier && $panel->isEnabled()) {
            $resources = $panel->getResources($server->getIdentifier());
            if ($resources) $state = $resources['current_state'];
        }
        $details = $server->external_identifier && $panel->isEnabled() ? $panel->getServerDetails($server->getIdentifier()) : null;
        $ws = $server->external_identifier && $panel->isEnabled() ? $panel->getWebsocketCredentials($server->getIdentifier()) : null;
        $this->view('servers/show', [
            'server' => $server,
            'resources' => $resources,
            'state' => $state,
            'details' => $details,
            'ws' => $ws,
            'simulator' => !$panel->isEnabled(),
            'panelType' => $panel->getTypeLabel(),
        ]);
    }

    public function console(string $id): void
    {
        $server = $this->findOwned((int)$id);
        $panel = PanelClient::instance();
        $ws = null;
        $state = $server->state;
        if ($server->external_identifier && $panel->isEnabled()) {
            $ws = $panel->getWebsocketCredentials($server->getIdentifier());
            $resources = $panel->getResources($server->getIdentifier());
            $state = $resources['current_state'] ?? $state;
        }
        $this->view('servers/console', ['server' => $server, 'ws' => $ws, 'state' => $state, 'simulator' => !$panel->isEnabled()]);
    }

    public function power(string $id): void
    {
        $this->requireCsrf();
        $server = $this->findOwned((int)$id);
        $action = (string)Request::post('action', 'start');
        if (!in_array($action, ['start', 'stop', 'restart', 'kill'], true)) {
            if (Request::wantsJson()) $this->json(['ok' => false, 'message' => 'Invalid action.']);
            Session::flash('error', 'Invalid action.');
            $this->redirect('/servers/' . $server->id);
        }
        $panel = PanelClient::instance();
        if ($server->external_identifier && $panel->isEnabled()) {
            $result = $panel->setPowerState($server->getIdentifier(), $action);
            if (!$result['ok']) {
                if (Request::wantsJson()) $this->json(['ok' => false, 'message' => $result['error']]);
                Session::flash('error', $result['error']);
                $this->redirect('/servers/' . $server->id);
            }
        }
        $newState = in_array($action, ['start', 'restart']) ? 'running' : 'stopped';
        $server->update(['state' => $newState]);
        if (Request::wantsJson()) $this->json(['ok' => true, 'state' => $newState]);
        Session::flash('success', ucfirst($action) . ' requested.');
        $this->redirect('/servers/' . $server->id);
    }

    public function command(string $id): void
    {
        $server = $this->findOwned((int)$id);
        $command = (string)Request::post('command', '');
        if (mb_strlen($command) > 200) $this->json(['ok' => false, 'message' => 'Command too long.']);
        $panel = PanelClient::instance();
        if ($server->external_identifier && $panel->isEnabled()) {
            $result = $panel->sendCommand($server->getIdentifier(), $command);
            if (!$result['ok']) $this->json(['ok' => false, 'message' => $result['error']]);
        }
        $this->json(['ok' => true, 'command' => $command]);
    }

    public function resources(string $id): void
    {
        $server = $this->findOwned((int)$id);
        $panel = PanelClient::instance();
        $resources = $server->external_identifier && $panel->isEnabled() ? $panel->getResources($server->getIdentifier()) : null;
        if (!$resources) $this->json(['ok' => true, 'state' => $server->state, 'resources' => null]);
        $this->json(['ok' => true, 'state' => $resources['current_state'], 'resources' => $resources]);
    }

    public function rename(string $id): void
    {
        $this->requireCsrf();
        $server = $this->findOwned((int)$id);
        $name = trim((string)$_POST['name'] ?? '');
        $v = new Validator();
        if (!$v->make(['name' => $name], ['name' => 'required|min:3|max:64'])) {
            Session::flash('error', $v->firstError());
            $this->redirect('/servers/' . $server->id);
        }
        $server->update(['name' => $name]);
        Session::flash('success', 'Server renamed.');
        $this->redirect('/servers/' . $server->id);
    }

    public function settings(string $id): void
    {
        $server = $this->findOwned((int)$id);
        $panel = PanelClient::instance();
        $this->view('servers/settings', [
            'server' => $server,
            'details' => $server->external_identifier && $panel->isEnabled() ? $panel->getServerDetails($server->getIdentifier()) : null,
        ]);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $server = $this->findOwned((int)$id);
        $panel = PanelClient::instance();
        if ($server->external_id && $panel->isEnabled()) {
            $panel->deleteServer($server->external_id);
        }
        $server->delete();
        Session::flash('success', 'Server deleted.');
        $this->redirect('/dashboard');
    }
}
