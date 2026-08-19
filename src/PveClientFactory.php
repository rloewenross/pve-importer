<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\PveClient;
use App\PveClientInfo;

class PveClientFactory {
    private HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        private string $pveStorage,
    ) {
        // don't check certificates since we are connecting to localhost
        $this->httpClient = $httpClient->withOptions([
            'verify_peer' => false,
            'verify_host' => false,
        ]);
    }

    public function newPveClient(string $username, string $password): PveClient {
        $client = new PveClient($this->httpClient, $this->pveStorage);
        $client->login($username, $password);
        return $client;
    }

    public function fromSession(SessionInterface $session): PveClient {
        $client = new PveClient($this->httpClient, $this->pveStorage);
        $client->setTicket($session->get('pve-ticket'));
        $client->setCsrf($session->get('pve-csrf'));
        $client->username = $session->get('pve-username');
        return $client;
    }

    public function fromInfo(PveClientInfo $info): PveClient {
        $client = new PveClient($this->httpClient, $this->pveStorage);
        $client->setTicket($info->ticket);
        $client->setCsrf($info->csrf);
        $client->username = $info->username;
        return $client;
    }
}
?>