<?php
namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;
use TusPhp\Events\TusEvent;
use App\PveClientInfo;

#[AsMessage('async')]
class DiskUploadMessage {
    public function __construct(
        public TusEvent $event,
        public PveClientInfo $clientInfo,
    ) {}
}
?>