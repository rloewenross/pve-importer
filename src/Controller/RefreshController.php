<?php
namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\PveClientFactory;

class RefreshController extends AbstractController {
    public function __construct(private PveClientFactory $pveClientFactory) {}

    #[Route('/refresh', methods: ['POST'], name: 'refresh')]
    public function refreshAuth(Request $request): Response {
        $this->pveClientFactory->fromSession($request->getSession())->refresh();
        return new Response();
    }
}
?>