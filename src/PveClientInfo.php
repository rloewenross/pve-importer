<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App;

class PveClientInfo { // class with only auth + username for easy serialization
    public function __construct(
        public string $ticket,
        public string $csrf,
        public string $username,
    ) {}
}
?>