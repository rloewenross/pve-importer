<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ImportController extends AbstractController {
    #[Route('/import', methods: ['GET'], name: 'import')]
    public function importPage(Request $request): Response {
        return $this->render('import.html.twig');
    }
}
?>