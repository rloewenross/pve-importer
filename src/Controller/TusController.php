<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use TusPhp\Tus\Server as TusServer;
use TusPhp\Events\TusEvent;
use App\PveClientFactory;
use App\Message\DiskUploadMessage;
use App\Entity\ImportStatus;

class TusController extends AbstractController {
    public function __construct(
        private PveClientFactory $pveClientFactory,
        private MessageBusInterface $bus,
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/tus', name: 'tus_post')]
    #[Route('/tus/{token}', name: 'tus', requirements: [ 'token' => '.+' ])]
    public function tus(Request $request, TusServer $server): Response {
        $session = $request->getSession();
        $client = $this->pveClientFactory->fromSession($session);
        $session->save();
        $bus = $this->bus;
        $slugger = $this->slugger;
        $entityManager = $this->entityManager;
        $server->event()->addListener('tus-server.upload.complete', function (TusEvent $event) use ($bus, $slugger, $client, $entityManager) {
            $oldFilePath = $event->getFile()->getFilePath();
            $safeFileName = $slugger->slug(basename($oldFilePath));
            $fileName = $safeFileName . '-' . uniqid();
            $filePath = dirname($oldFilePath) . '/' . $fileName;
            if (!rename($oldFilePath, $filePath)) {
                $filesystem = new Filesystem();
                $filesystem->remove($oldFilePath);
                throw new \RuntimeException("Failed to rename " . $oldFilePath . " to " . $filePath . ".");
            }
            $vmName = basename($oldFilePath);

            $importStatus = new ImportStatus($client->username, $vmName);
            $importStatus->setImporting();
            $entityManager->persist($importStatus);
            $entityManager->flush();

            $pool = $event->getFile()->details()['metadata']['pool'];
            $bus->dispatch(new DiskUploadMessage($filePath, $vmName, $client->toInfo(), $importStatus->getId(), $pool));
        });
        return $server->serve();
    }
}