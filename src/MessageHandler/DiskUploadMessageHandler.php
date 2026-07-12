<?php
namespace App\MessageHandler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\PveClientFactory;
use App\Message\DiskUploadMessage;
use App\Entity\ImportStatus;
use App\Entity\UserInfo;

#[AsMessageHandler]
class DiskUploadMessageHandler {
    public function __construct(
        private MessageBusInterface $bus,
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager,
        private PveClientFactory $pveClientFactory,
    ) {}

    public function __invoke(DiskUploadMessage $message) {
        $client = $this->pveClientFactory->fromInfo($message->clientInfo);

        $oldFilePath = $message->event->getFile()->getFilePath();
        $safeFileName = $this->slugger->slug(basename($oldFilePath));
        $fileName = $safeFileName . '-' . uniqid();
        $filePath = dirname($oldFilePath) . '/' . $fileName;
        
        rename($oldFilePath, $filePath);
        
        $nodeName = strtok(gethostname(), '.');
        $newVmid = $client->getFreeVmid();
        
        $importStatus = new ImportStatus();
        $importStatus->setError(false);
        
        $createVmResponse = $client->api('POST', '/nodes/' . $nodeName . '/qemu', [ 'vmid' => $newVmid, 'name' => basename($oldFilePath) ]);
        if ($createVmResponse->getStatusCode() != 200) {
            $importStatus->setError(true);
            $importStatus->setErrorMessage($createVmResponse->toArray()['data']['error']);
        }
        
        $this->entityManager->persist($importStatus);
        $userInfo = $this->entityManager->getRepository(UserInfo::class)->findOneBy(['user_id' => $message->username]);
        $userInfo->appendImportStatusId($importStatus->getId());
        
        $this->entityManager->flush();
        
        if (!$importStatus->isError()) {
            $this->bus->dispatch(new DiskImportMessage($newVmid, $filePath, $client->toInfo(), $importStatus->getId()));
        }
    }
}
?>