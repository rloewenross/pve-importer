<?php
namespace App;

class PveClientInfo { // class with only auth + username for easy serialization
    public function __construct(
        public string $ticket,
        public string $csrf,
        public string $username,
    ) {}
}
?>