<?php
namespace App;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\PveClient;

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
    
    public function newPveClient(string $username, string $password) {
        $client = new PveClient($this->httpClient, $this->pveStorage);
        $client->login($username, $password);
        return $client;
    }
    
    public function fromSession(SessionInterface $session) {
        $client = new PveClient($this->httpClient, $this->pveStorage);
        $client->setTicket($session->get('pve-ticket'));
        $client->setCsrf($session->get('pve-csrf'));
        $client->username = $session->get('pve-username');
        return $client;
    }
}
?>