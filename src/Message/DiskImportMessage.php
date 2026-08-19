<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

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