<?php
namespace App\MessageHandler;

use App\Message\DiskImportMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Filesystem\Filesystem;

#[AsMessageHandler]
class DiskImportMessageHandler {
    public function __invoke(DiskImportMessage $message) {
        $process = new Process(['sudo', '-n', '/usr/sbin/qm', 'disk', 'import', $message->vmId, $message->filePath, $message->client->pveStorage, '--target-disk', 'scsi0']);
        $process->run();
        
        $filesystem = new Filesystem();
        $filesystem->remove($message->filePath);
    }
}
?>