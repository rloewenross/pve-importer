<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Root extends AbstractController {
    #[Route('/', methods: ['GET'], name: 'root')]
    public function rootPage(): RedirectResponse {
        return new RedirectResponse($this->generateUrl('import'));
    }
}
?>