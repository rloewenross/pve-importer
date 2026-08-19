<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ImportStatus;
use App\Security\PveUser;

class ImportStatusController extends AbstractController {
    #[Route('/import_status', methods: ['GET'], name: 'import_status')]
    public function status(EntityManagerInterface $entityManager, #[CurrentUser] PveUser $user, Request $request): JsonResponse {
        $importStatusRepository = $entityManager->getRepository(ImportStatus::class);
        $statusList =  $importStatusRepository->findBy([ 'pve_user_id' => $user->getUsername() ]);
        $responseArray = \array_map(
            function ($status) {
                return [
                    'vm_name' => $status->getVmName(),
                    'vmid' => $status->getVmid(),
                    'state' => $status->getState(),
                    'error_message' => $status->getErrorMessage(),
                    'date_created' => $status->getDateCreated()->getTimestamp(),
                ];
            },
            $statusList
        );

        $flush = false;
        foreach ($statusList as $status) {
            if ($status->isComplete() || $status->isErrorOccurred()) { # since the status is done and we are giving it to the client we can remove it
                $entityManager->remove($status);
                $flush = true;
            }
        }
        if ($flush) {
            $entityManager->flush();
        }

        return new JsonResponse($responseArray);
    }
}
?>