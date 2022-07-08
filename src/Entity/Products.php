<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Nullable;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $ProdName;

    #[ORM\Column(type: 'string', length: 255)]
    private $ProdSlug;

    #[ORM\Column(type: 'json')]
    private $ProdIllustarion;

    #[ORM\Column(type: 'float')]
    private $ProdPrice;

    #[ORM\Column(type: 'text')]
    private $ProdDescription;

    #[ORM\Column(type: 'boolean')]
    private $status;

    #[ORM\Column(type: 'datetime')]
    private $Created_At;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $Updated_At;

    #[ORM\ManyToOne(targetEntity: Categorys::class, inversedBy: 'products')]
    private $category;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'products')]
    private $OwnedBy;

    #[ORM\ManyToOne(targetEntity: UserShop::class, inversedBy: 'products')]
    private $BelongsToShop;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $ProdImgView;

    #[ORM\ManyToOne(targetEntity: SubCategories::class, inversedBy: 'products')]
    private $SubCategory;


    public function MyIllustratorToArray(): ?array
    {
        return json_decode($this->ProdIllustarion) ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProdName(): ?string
    {
        return $this->ProdName;
    }

    public function setProdName(string $ProdName): self
    {
        $this->ProdName = $ProdName;

        return $this;
    }

    public function getProdSlug(): ?string
    {
        return $this->ProdSlug;
    }

    public function setProdSlug(string $ProdSlug): self
    {
        $this->ProdSlug = $ProdSlug;

        return $this;
    }

    public function getProdIllustarion(): ?string
    {
        return $this->ProdIllustarion;
    }

    public function setProdIllustarion(string $ProdIllustarion): self
    {
        $this->ProdIllustarion = $ProdIllustarion;

        return $this;
    }

    public function getProdPrice(): ?float
    {
        return $this->ProdPrice;
    }

    public function setProdPrice(float $ProdPrice): self
    {
        $this->ProdPrice = $ProdPrice;

        return $this;
    }

    public function getProdDescription(): ?string
    {
        return $this->ProdDescription;
    }

    public function setProdDescription(string $ProdDescription): self
    {
        $this->ProdDescription = $ProdDescription;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->Created_At;
    }

    public function setCreatedAt(\DateTimeInterface $Created_At): self
    {
        $this->Created_At = $Created_At;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->Updated_At;
    }

    public function setUpdatedAt(?\DateTimeInterface $Updated_At): self
    {
        $this->Updated_At = $Updated_At;

        return $this;
    }

    public function getCategory(): ?Categorys
    {
        return $this->category;
    }

    public function setCategory(?Categorys $category): self
    {
        $this->category = $category;

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

    public function getBelongsToShop(): ?UserShop
    {
        return $this->BelongsToShop;
    }

    public function setBelongsToShop(?UserShop $BelongsToShop): self
    {
        $this->BelongsToShop = $BelongsToShop;

        return $this;
    }

    public function getProdImgView(): ?string
    {
        return $this->ProdImgView;
    }

    public function setProdImgView(?string $ProdImgView): self
    {
        $this->ProdImgView = $ProdImgView;

        return $this;
    }

    public function getSubCategory(): ?SubCategories
    {
        return $this->SubCategory;
    }

    public function setSubCategory(?SubCategories $SubCategory): self
    {
        $this->SubCategory = $SubCategory;

        return $this;
    }
}
