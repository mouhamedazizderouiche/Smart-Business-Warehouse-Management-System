<?php

namespace App\Entity;

use App\Repository\CommentaireEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireEventRepository::class)]
class CommentaireEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu du commentaire est obligatoire.')]
    #[Assert\Length(
        min: 5,
        minMessage: 'Le commentaire doit contenir au moins {{ limit }} caractères.'
    )]
    private ?string $contenu = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commentaireEvents', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'commentaireEvents', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evenement $evenement = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }
}
