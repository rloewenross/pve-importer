<?php
namespace App\MessageHandler;

use App\Message\DiskImportMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Entity\ImportStatus;
use App\PveClientFactory;

#[AsMessageHandler]
class DiskImportMessageHandler {
    public function __construct(
        private PveClientFactory $pveClientFactory,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(DiskImportMessage $message): void {
        $client = $this->pveClientFactory->fromInfo($message->clientInfo);
        $process = new Process(['sudo', '-n', '/usr/sbin/qm', 'disk', 'import', $message->vmId, $message->filePath, $client->pveStorage, '--target-disk', 'scsi0']);
        $process->setTimeout(null);
        $process->setIdleTimeout(600);
        $process->run();

        $filesystem = new Filesystem();
        $filesystem->remove($message->filePath);

        if (!$process->isSuccessful()) {
            $status = $this->entityManager->getRepository(ImportStatus::class)->find($message->importStatusId);
            $status->setErrorOccurred();
            $status->setErrorMessage("qm disk import command failed: " . $process->getErrorOutput());
            $this->entityManager->flush();
            return;
        }

        $status = $this->entityManager->getRepository(ImportStatus::class)->find($message->importStatusId);
        $status->setComplete();
        $this->entityManager->flush();
    }
}
?>