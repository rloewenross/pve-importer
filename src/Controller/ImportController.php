<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\PveClientFactory;
use App\PveInvalidTicketException;

class ImportController extends AbstractController {
    public function __construct(private PveClientFactory $pveClientFactory) {}

    #[Route('/import', methods: ['GET'], name: 'import')]
    public function importPage(Request $request): Response {
        $session = $request->getSession();
        $client = $this->pveClientFactory->fromSession($session);
        $session->save();

        try {
            $response = $this->render('import.html.twig', [
                'username' => $client->username,
                'pools' => $client->getPools(),
                'storages' => $client->getStorages(),
            ]);
        } catch (PveInvalidTicketException $e) {
            return new RedirectResponse($this->generateUrl('login'));
        }

        return $response;
    }
}
?>