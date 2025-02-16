<?php
namespace App\Entity;

use App\Repository\ReclamationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use StatutReclamation;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: ReclamationsRepository::class)]
#[ORM\HasLifecycleCallbacks] 
class Reclamations
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid")]
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateReclamation = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "The rate cannot be null.")]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: "The rate must be between 1 and 5.")]
    private ?int $rate = 1;

    #[ORM\Column(length: 255)]
        #[Assert\NotBlank(message: "The title cannot be empty.")]
        #[Assert\Length(
            min: 5, 
            max: 255, 
            minMessage: "The title must be at least {{ limit }} characters long.",
            maxMessage: "The title cannot be longer than {{ limit }} characters."
        )]
        #[Assert\Regex(
            pattern: "/^\S+\s+\S+.*$/",
            message: "The title must contain at least two words."
        )]
        private ?string $title = null;

        #[ORM\Column(length: 255)]
        #[Assert\NotBlank(message: "The description cannot be empty.")]
        #[Assert\Length(
            min: 10, 
            max: 100, 
            minMessage: "The description must be at least {{ limit }} characters long.",
            maxMessage: "The description cannot be longer than {{ limit }} characters."
        )]
        private ?string $description = null;
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reclamations')]
        #[ORM\JoinColumn(nullable: false)]
        private ?User $user = null;
    #[ORM\Column(enumType: StatutReclamation::class)]
    private StatutReclamation $statut;
    

    /**
     * @var Collection<int, MessageReclamation>
     */
    #[ORM\OneToMany(targetEntity: MessageReclamation::class, mappedBy: 'reclamation')]
    private Collection $reclamations;

    public function __construct()
    {
        $this->reclamations = new ArrayCollection();
        $this->statut = StatutReclamation::EN_ATTENTE;
    }

    #[ORM\PrePersist] 
    public function generateUuid(): void
    {
        if ($this->id === null) {
            $this->id = Uuid::v4();  
        }
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;
        return $this;
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

    public function getDateReclamation(): ?\DateTimeInterface
    {
        return $this->dateReclamation;
    }

    public function setDateReclamation(\DateTimeInterface $dateReclamation): static
    {
        $this->dateReclamation = $dateReclamation;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatut(): StatutReclamation
    {
        return $this->statut;
    }

    public function setStatut(StatutReclamation $statut): void
    {
        $this->statut = $statut;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(MessageReclamation $reclamation): static
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations->add($reclamation);
            $reclamation->setReclamation($this);
        }
        return $this;
    }

    public function removeReclamation(MessageReclamation $reclamation): static
    {
        if ($this->reclamations->removeElement($reclamation)) {
            if ($reclamation->getReclamation() === $this) {
                $reclamation->setReclamation(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->description;
    }
        public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): static
    {
        $this->rate = $rate;
        return $this;
}
}
