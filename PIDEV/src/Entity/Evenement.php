<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Enum\TypeEvenement;
use App\Enum\StatutEvenement;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?Uuid $id = null;
    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de début est obligatoire.')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de fin est obligatoire.')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: EvenementRegion::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $evenementRegions;

    public function __construct()
    {
        $this->evenementRegions = new ArrayCollection();
    }

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getType(): ?TypeEvenement
    {
        return $this->type ? TypeEvenement::tryFrom($this->type) : null;
    }

    public function setType(?TypeEvenement $type): self
    {
        $this->type = $type?->value;
        return $this;
    }

    public function getStatut(): ?StatutEvenement
    {
        return $this->statut ? StatutEvenement::tryFrom($this->statut) : null;
    }

    public function setStatut(?StatutEvenement $statut): self
    {
        $this->statut = $statut?->value;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    // Gestion des régions via EvenementRegion

    /**
     * @return Collection<int, EvenementRegion>
     */
    public function getEvenementRegions(): Collection
    {
        return $this->evenementRegions;
    }

    public function addEvenementRegion(EvenementRegion $evenementRegion): self
    {
        if (!$this->evenementRegions->contains($evenementRegion)) {
            $this->evenementRegions->add($evenementRegion);
            $evenementRegion->setEvenement($this);
        }

        return $this;
    }

    public function removeEvenementRegion(EvenementRegion $evenementRegion): self
    {
        if ($this->evenementRegions->removeElement($evenementRegion)) {
            if ($evenementRegion->getEvenement() === $this) {
                $evenementRegion->setEvenement(null);
            }
        }

        return $this;
    }

    // Méthode pour obtenir les régions associées directement
    public function getRegions(): array
    {
        $regions = [];
        foreach ($this->evenementRegions as $evenementRegion) {
            $regions[] = $evenementRegion->getRegion();
        }
        return $regions;
    }

    // Méthode pour ajouter une région directement
    public function addRegion(Region $region): self
    {
        $evenementRegion = new EvenementRegion();
        $evenementRegion->setRegion($region);
        $this->addEvenementRegion($evenementRegion);
        return $this;
    }

    // Méthode pour supprimer une région directement
    public function removeRegion(Region $region): self
    {
        foreach ($this->evenementRegions as $evenementRegion) {
            if ($evenementRegion->getRegion() === $region) {
                $this->removeEvenementRegion($evenementRegion);
                break;
            }
        }
        return $this;
    }
}