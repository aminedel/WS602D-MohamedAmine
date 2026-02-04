<?php

namespace App\Entity;

use App\Repository\GenerationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GenerationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Generation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'generations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $file = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $sourceUrl = null;

    /**
     * @var Collection<int, GenerationUserContact>
     */
    #[ORM\OneToMany(targetEntity: GenerationUserContact::class, mappedBy: 'generation', orphanRemoval: true)]
    private Collection $generationUserContacts;

    public function __construct()
    {
        $this->generationUserContacts = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): static
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    /**
     * @return Collection<int, GenerationUserContact>
     */
    public function getGenerationUserContacts(): Collection
    {
        return $this->generationUserContacts;
    }

    public function addGenerationUserContact(GenerationUserContact $generationUserContact): static
    {
        if (!$this->generationUserContacts->contains($generationUserContact)) {
            $this->generationUserContacts->add($generationUserContact);
            $generationUserContact->setGeneration($this);
        }

        return $this;
    }

    public function removeGenerationUserContact(GenerationUserContact $generationUserContact): static
    {
        if ($this->generationUserContacts->removeElement($generationUserContact)) {
            // set the owning side to null (unless already changed)
            if ($generationUserContact->getGeneration() === $this) {
                $generationUserContact->setGeneration(null);
            }
        }

        return $this;
    }
}
