<?php
namespace App\MessageHandler;

use App\Message\DiskImportMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
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
        $filesystem = new Filesystem();
        $status = $this->entityManager->getRepository(ImportStatus::class)->find($message->importStatusId);
        $client = $this->pveClientFactory->fromInfo($message->clientInfo);

        $filePath = $message->filePath;
        $origFilePath = $filePath;

        if (!$client->checkPermission("/storage/" . $client->pveStorage, "Datastore.Allocate")) {
            $status->setErrorOccurred();
            $status->setErrorMessage("Missing permissions to allocate data");

            $filesystem->remove($origFilePath);
            return;
        }

        $isZip = false;
        if (\mime_content_type($filePath) === "application/zip") {
            $zip = new \ZipArchive();
            $zipDestPath = $filePath . '-extract';
            $filesystem->mkdir($zipDestPath);
            if (!$zip->open($filePath) || !$zip->extractTo($zipDestPath)) {
                $status->setErrorOccurred();
                $status->setErrorMessage("Failed to extract zip file");
                $this->entityManager->flush();

                $filesystem->remove($origFilePath);
                $filesystem->remove($zipDestPath);
                return;
            }
            $zip->close();

            $finder = new Finder();
            $finder->files()->in($zipDestPath)->name(["*.qcow2", "*.raw", "*.vmdk"]);
            $filePath = null;
            $filePathLength = 0;
            foreach ($finder as $file) {
                $strLen = \strlen($file->getFilename()); # the file with the shortest name should be the correct file to import in the case of
                                                         # vmdk across multiple files as the other vmdk files will have -s00X appended to the filename
                if ($filePath === null || $strLen < $filePathLength) {
                    $filePath = $file->getPathname();
                    $filePathLength = $strLen;
                }
            }
            if ($filePath === null) {
                $status->setErrorOccurred();
                $status->setErrorMessage("Failed to find disk image in zip file");
                $this->entityManager->flush();

                $filesystem->remove($origFilePath);
                $filesystem->remove($zipDestPath);
                return;
            }

            $isZip = true;
        }

        $process = new Process(['sudo', '-n', '/usr/sbin/qm', 'disk', 'import', $message->vmId, $filePath, $client->pveStorage, '--target-disk', 'scsi0']);
        $process->setTimeout(null);
        $process->setIdleTimeout(600);
        $process->run();

        $filesystem->remove($origFilePath);
        if ($isZip) {
            $filesystem->remove($zipDestPath);
        }

        if (!$process->isSuccessful()) {
            $status->setErrorOccurred();
            $status->setErrorMessage("qm disk import command failed: " . $process->getErrorOutput());
            $this->entityManager->flush();
            return;
        }

        $status->setComplete();
        $this->entityManager->flush();
    }
}
?>