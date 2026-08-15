<?php

namespace App\Entity;

use App\Repository\ImportStatusRepository;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(length: 255)]
    private ?string $vm_name = null;

    #[ORM\Column]
    private ?int $vmid = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date_created = null;

    public function __construct(string $pve_user_id, string $vm_name, int $vmid) {
        $this->error_occurred = false;
        $this->complete = false;
        $this->pve_user_id = $pve_user_id;
        $this->vm_name = $vm_name;
        $this->vmid = $vmid;
        $this->date_created = \DateTimeImmutable::createFromTimestamp(\time());
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

    public function getVmName(): ?string
    {
        return $this->vm_name;
    }

    public function setVmName(string $vm_name): static
    {
        $this->vm_name = $vm_name;

        return $this;
    }

    public function getVmid(): ?int
    {
        return $this->vmid;
    }

    public function setVmid(int $vmid): static
    {
        $this->vmid = $vmid;

        return $this;
    }

    public function getDateCreated(): ?\DateTime
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTime $date_created): static
    {
        $this->date_created = $date_created;

        return $this;
    }
}
