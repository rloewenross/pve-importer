<?php
namespace App\MessageHandler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\PveClientFactory;
use App\Message\DiskUploadMessage;
use App\Message\DiskImportMessage;
use App\Entity\ImportStatus;

#[AsMessageHandler]
class DiskUploadMessageHandler {
    public function __construct(
        private MessageBusInterface $bus,
        private EntityManagerInterface $entityManager,
        private PveClientFactory $pveClientFactory,
    ) {}

    public function __invoke(DiskUploadMessage $message): void {
        $client = $this->pveClientFactory->fromInfo($message->clientInfo);
        $importStatus = $this->entityManager->getRepository(ImportStatus::class)->find($message->statusId);

        $nodeName = strtok(gethostname(), '.');
        try {
            $newVmid = $client->getFreeVmid();
        } catch (\Exception $e) {
            $importStatus->setErrorOccurred();
            $importStatus->setErrorMessage($e->getMessage());
            $this->entityManager->flush();
            return;
        }
        
        $importStatus->setVmid($newVmid);
        $this->entityManager->flush();

        try {
            $createVmResponse = $client->api('POST', '/nodes/' . $nodeName . '/qemu', ['vmid' => $newVmid, 'name' => $message->vmName]);

            if ($createVmResponse->getStatusCode() != 200) {
                $importStatus->setErrorOccurred();
                $importStatus->setErrorMessage($createVmResponse->toArray()['data']['error']);
            }
        } catch (\Exception $e) {
            $importStatus->setErrorOccurred();
            $importStatus->setErrorMessage($e->getMessage());
        }

        $this->entityManager->flush();

        if (!$importStatus->isErrorOccurred()) {
            $this->bus->dispatch(new DiskImportMessage($newVmid, $message->path, $client->toInfo(), $message->statusId));
        }
    }
}
?>