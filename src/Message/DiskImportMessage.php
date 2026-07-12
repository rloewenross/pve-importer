<?php
namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;
use App\PveClientInfo;

#[AsMessage('async')]
class DiskImportMessage {
    public function __construct(
        public string $vmId,
        public string $filePath,
        public PveClientInfo $clientInfo,
        public int $importStatusId,
    ) {}
}
?>