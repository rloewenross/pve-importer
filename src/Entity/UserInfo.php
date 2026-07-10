<?php

namespace App\Entity;

use App\Repository\UserInfoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserInfoRepository::class)]
class UserInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private array $import_status_ids = [];

    #[ORM\Column(length: 255)]
    private ?string $user_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImportStatusIds(): array
    {
        return $this->import_status_ids;
    }

    public function setImportStatusIds(array $import_status_ids): static
    {
        $this->import_status_ids = $import_status_ids;

        return $this;
    }

    public function appendImportStatusId(int $id) {
        \array_push($this->import_status_ids, $id);
    }

    public function getUserId(): ?string
    {
        return $this->user_id;
    }

    public function setUserId(string $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }
}
