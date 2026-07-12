<?php
namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;
use TusPhp\Events\TusEvent;
use App\PveClientInfo;

#[AsMessage('async')]
class DiskUploadMessage {
    public function __construct(
        private TusEvent $event,
        private PveClientInfo $clientInfo,
    ) {}
}
?>