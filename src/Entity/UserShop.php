<?php

namespace App\Entity;

use App\Repository\UserShopRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserShopRepository::class)]
class UserShop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $Shop_Name;

    #[ORM\Column(type: 'datetime')]
    private $Created_At;

    #[ORM\Column(type: 'boolean')]
    private $status;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    private $OwnedBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShopName(): ?string
    {
        return $this->Shop_Name;
    }

    public function setShopName(string $Shop_Name): self
    {
        $this->Shop_Name = $Shop_Name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->Created_At;
    }

    public function setCreatedAt(\DateTimeInterface $Created_At): self
    {
        $this->Created_At = $Created_At;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOwnedBy(): ?User
    {
        return $this->OwnedBy;
    }

    public function setOwnedBy(?User $OwnedBy): self
    {
        $this->OwnedBy = $OwnedBy;

        return $this;
    }
}
