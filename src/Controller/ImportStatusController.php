<?php
namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImportStatusController extends AbstractController {
    #[Route('/import_status', methods: ['GET'], name: 'import_status')]
    public function status(Request $request): JsonResponse {
        $session = $request->getSession();
        $status = $session->get('import-status') ?? 'ok';
        if ($status == 'error') {
            $message = $session->get('import-error');
        } else {
            $message = '';
        }
        return new JsonResponse([
            'status' => $status,
            'message' => $message,
        ]);
    }
}
?>