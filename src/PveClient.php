<?php
namespace App;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
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
    ) {
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
    
    public function setTicket(string $ticket) {
        $this->ticket = $ticket;
    }
    
    public function setCsrf(string $csrf) {
        $this->csrf = $csrf;
    }
    
    public function toSession(SessionInterface $session) {
        $session->set('pve-ticket', $this->ticket);
        $session->set('pve-csrf', $this->csrf);
        $session->set('pve-username', $this->username);
    }
    
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
        return new PveClientInfo($this->ticket, $this->csrf, $this->username, $this->pveStorage);
    }
}
?>