<?php

namespace App\Entity;

use App\Repository\MessageReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MessageReclamationRepository::class)]
#[ORM\HasLifecycleCallbacks] 
class MessageReclamation
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid")]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateMessage = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    private ?Reclamations $reclamation = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDateMessage(): ?\DateTimeInterface
    {
        return $this->dateMessage;
    }

    public function setDateMessage(\DateTimeInterface $dateMessage): static
    {
        $this->dateMessage = $dateMessage;

        return $this;
    }

    public function getReclamation(): ?Reclamations
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamations $reclamation): static
    {
        $this->reclamation = $reclamation;

        return $this;
    }

    #[ORM\PrePersist]  // Lifecycle callback to auto-generate UUID
    public function generateUuid(): void
    {
        if ($this->id === null) {
            $this->id = Uuid::v4();  // Generate UUID if not already set
        }
    }
}
