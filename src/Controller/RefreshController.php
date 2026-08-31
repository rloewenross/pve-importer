<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\PveClientFactory;
use App\PveInvalidTicketException;

class RefreshController extends AbstractController {
    public function __construct(private PveClientFactory $pveClientFactory) {}

    #[Route('/refresh', methods: ['POST'], name: 'refresh')]
    public function refreshAuth(Request $request): Response {
        try {
            $this->pveClientFactory->fromSession($request->getSession())->refresh();
        } catch (PveInvalidTicketException $e) {
            return new RedirectResponse($this->generateUrl('login'));
        }
        return new Response();
    }
}
?>