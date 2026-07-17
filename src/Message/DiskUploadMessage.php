<?php
namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;
use TusPhp\Events\TusEvent;
use App\PveClientInfo;

#[AsMessage('async')]
class DiskUploadMessage {
    public function __construct(
        public string $path,
        public string $vmName,
        public PveClientInfo $clientInfo,
    ) {}
}
?>