<?php
namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class DiskImportMessage {
    public function __construct(
        public string $vmId,
        public string $filePath,
        public string $pveStorage,
    ) {}
}
?>