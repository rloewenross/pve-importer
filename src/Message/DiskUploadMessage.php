<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;
use App\PveClientInfo;

#[AsMessage('async')]
class DiskUploadMessage {
    public function __construct(
        public string $path,
        public string $vmName,
        public PveClientInfo $clientInfo,
        public int $statusId,
        public string $pool,
    ) {}
}
?>