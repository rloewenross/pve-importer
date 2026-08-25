<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\PveClientInfo;

class PveClient {
    private string $ticket;
    private string $csrf;
    public string $username;

    public function __construct(
        private HttpClientInterface $httpClient,
        public string $pveStorage,
    ) {}

    public function login(
        string $username,
        string $password,
    ): void {
        $loginResponse = $this->httpClient->request(
            'POST',
            'https://localhost:8006/api2/json/access/ticket',
            [ 'json' => [ 'username' => $username, 'password' => $password, ] ],
        );

        if ($loginResponse->getStatusCode() != 200) {
            throw new CustomUserMessageAuthenticationException('Invalid Proxmox credentials');
        }

        $loginResponseData = $loginResponse->toArray()['data'];
        $this->ticket = $loginResponseData['ticket'];
        $this->csrf = $loginResponseData['CSRFPreventionToken'];

        $this->username = $username;
    }

    public function setTicket(string $ticket): void {
        $this->ticket = $ticket;
    }

    public function setCsrf(string $csrf): void {
        $this->csrf = $csrf;
    }

    public function toSession(SessionInterface $session): void {
        $session->set('pve-ticket', $this->ticket);
        $session->set('pve-csrf', $this->csrf);
        $session->set('pve-username', $this->username);
    }

    /**
     * @param mixed[] $data
     */
    public function api(string $method, string $path, array $data): ResponseInterface {
        $options = [
                'headers' => [
                    'CSRFPreventionToken' => $this->csrf,
                    'Cookie' => sprintf("%s=%s", 'PVEAuthCookie', rawurlencode($this->ticket)),
                ],
        ];
        if ($method == 'GET') {
            $options['query'] = $data;
        } else {
            $options['json'] = $data;
        }

        return $this->httpClient->request(
            $method,
            'https://localhost:8006/api2/json' . $path,
            $options,
        );
    }

    public function getFreeVmid(): int {
        $resourcesResponse = $this->api('GET', '/cluster/resources', [ 'type' => 'vm' ]);
        $resources = $resourcesResponse->toArray()['data'];
        $vmids = array();
        foreach ($resources as $resource) {
            $vmid = $resource['vmid'];
            array_push($vmids, $vmid);
        }
        sort($vmids, SORT_NUMERIC);

        $newid = 100;
        foreach ($vmids as $vmid) {
            if ($newid == $vmid) {
                $newid += 1;
            }
        }

        return $newid;
    }

    public function toInfo(): PveClientInfo {
        return new PveClientInfo($this->ticket, $this->csrf, $this->username);
    }

    public function refresh(): void {
        $refreshResponse = $this->api('POST', '/access/ticket', [
            'username' => $this->username,
            'password' => $this->ticket,
        ]);
        if ($refreshResponse->getStatusCode() !== 200) {
            throw new \RuntimeException('Failed to refresh user');
        }
        $refreshResponse = $refreshResponse->toArray()['data'];
        $this->ticket = $refreshResponse['ticket'];
        $this->csrf = $refreshResponse['CSRFPreventionToken'];
    }

    public function checkPermission(string $path, string $privs): bool {
        $permissionResponse = $this->api('POST', '/access/ticket', [
            'username' => $this->username,
            'password' => $this->ticket,
            'path' => $path,
            'privs' => $privs,
        ]);

        if ($permissionResponse->getStatusCode() === 200) {
            return true;
        } else {
            return false;
        }
    }

    public function getPools(): array {
        $poolResponse = $this->api('GET', '/pools', []);

        if ($poolResponse->getStatusCode() !== 200) {
            throw new \RuntimeException('Failed to get pools');
        }

        $poolResponseData = $poolResponse->toArray()['data'];
        $pools = [];
        foreach ($poolResponseData as $poolParams) {
            array_push($pools, $poolParams['poolid']);
        }

        return $pools;
    }
}
?>