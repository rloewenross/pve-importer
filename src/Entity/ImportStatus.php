<?php

namespace App\Entity;

use App\Repository\ImportStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportStatusRepository::class)]
class ImportStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $error = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $error_message = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isError(): ?bool
    {
        return $this->error;
    }

    public function setError(bool $error): static
    {
        $this->error = $error;

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
}
