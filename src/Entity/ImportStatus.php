<?php

namespace App\Entity;

use App\Repository\ImportStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportStatusRepository::class)]
#[ORM\Index(name: 'idx_import_status_pve_user_id', columns: ['pve_user_id'])]
class ImportStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $pve_user_id = null;

    #[ORM\Column]
    private ?bool $error_occurred = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $error_message = null;

    #[ORM\Column]
    private ?bool $complete = null;
    
    public function __construct(string $pve_user_id) {
        $this->error_occurred = false;
        $this->complete = false;
        $this->pve_user_id = $pve_user_id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPveUserId(): ?string
    {
        return $this->pve_user_id;
    }

    public function setPveUserId(string $pve_user_id): static
    {
        $this->pve_user_id = $pve_user_id;

        return $this;
    }

    public function isErrorOccurred(): ?bool
    {
        return $this->error_occurred;
    }

    public function setErrorOccurred(bool $error_occurred): static
    {
        $this->error_occurred = $error_occurred;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->error_message;
    }

    public function setErrorMessage(?string $error_message): static
    {
        $this->error_message = $error_message;

        return $this;
    }

    public function isComplete(): ?bool
    {
        return $this->complete;
    }

    public function setComplete(bool $complete): static
    {
        $this->complete = $complete;

        return $this;
    }
}
