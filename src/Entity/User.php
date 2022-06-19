<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    private $FirstName;

    #[ORM\Column(type: 'string', length: 255)]
    private $LastName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $UserProfilePhoto;

    #[ORM\Column(type: 'datetime')]
    private $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updated_at;

    #[ORM\Column(type: 'boolean')]
    private $Activation;

    #[ORM\Column(type: 'date')]
    private $BirthDay;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $Tel;

    #[ORM\Column(type: 'string', length: 255)]
    private $Gender;

    #[ORM\OneToMany(mappedBy: 'OwnedBy', targetEntity: Products::class)]
    private $products;

    #[ORM\OneToOne(targetEntity: UserShop::class, cascade: ['persist', 'remove'])]
    private $UserShop;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $EmailVerified;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $EmailMsgChecker;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->FirstName;
    }

    public function setFirstName(string $FirstName): self
    {
        $this->FirstName = $FirstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->LastName;
    }

    public function setLastName(string $LastName): self
    {
        $this->LastName = $LastName;

        return $this;
    }

    public function getUserProfilePhoto(): ?string
    {
        return $this->UserProfilePhoto;
    }

    public function setUserProfilePhoto(string $UserProfilePhoto): self
    {
        $this->UserProfilePhoto = $UserProfilePhoto;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function isActivation(): ?bool
    {
        return $this->Activation;
    }

    public function setActivation(bool $Activation): self
    {
        $this->Activation = $Activation;

        return $this;
    }

    public function getBirthDay(): ?\DateTimeInterface
    {
        return $this->BirthDay;
    }

    public function setBirthDay(\DateTimeInterface $BirthDay): self
    {
        $this->BirthDay = $BirthDay;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->Tel;
    }

    public function setTel(?string $Tel): self
    {
        $this->Tel = $Tel;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->Gender;
    }

    public function setGender(string $Gender): self
    {
        $this->Gender = $Gender;

        return $this;
    }

    /**
     * @return Collection<int, Products>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Products $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setOwnedBy($this);
        }

        return $this;
    }

    public function removeProduct(Products $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getOwnedBy() === $this) {
                $product->setOwnedBy(null);
            }
        }

        return $this;
    }

    public function getUserShop(): ?UserShop
    {
        return $this->UserShop;
    }

    public function setUserShop(?UserShop $UserShop): self
    {
        $this->UserShop = $UserShop;

        return $this;
    }

    public function isEmailVerified(): ?bool
    {
        return $this->EmailVerified;
    }

    public function setEmailVerified(?bool $EmailVerified): self
    {
        $this->EmailVerified = $EmailVerified;

        return $this;
    }

    public function getEmailMsgChecker(): ?string
    {
        return $this->EmailMsgChecker;
    }

    public function setEmailMsgChecker(?string $EmailMsgChecker): self
    {
        $this->EmailMsgChecker = $EmailMsgChecker;

        return $this;
    }
}
