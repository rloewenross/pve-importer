<?php
namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends AbstractController {
    #[Route('/login', methods: ['GET', 'POST'], name: 'login')]
    public function loginPage(Request $request): Response {
        return $this->render('login.html.twig');
    }
}
?>