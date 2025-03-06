<?php

namespace App\Entity;

use App\Repository\RegionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la région ne peut pas être vide.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom de la région ne peut pas dépasser 100 caractères.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z][a-zA-Z0-9\s\-]*$/",
        message: "Le nom de la région doit commencer par une lettre et ne contenir que des lettres, des chiffres, des espaces et des tirets."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La ville ne peut pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "Le nom de la ville ne peut pas dépasser 50 caractères.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z][a-zA-Z\s\-]*$/",
        message: "La ville doit commencer par une lettre et ne peut contenir que des lettres, des espaces et des tirets."
    )]
    private ?string $ville = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description de la région ne peut pas être vide.")]
    #[Assert\Length(max: 500, maxMessage: "La description ne peut pas dépasser 500 caractères.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s\.,\-]*$/",
        message: "La description ne peut contenir que des lettres, des chiffres, des espaces, des virgules, des points et des tirets."
    )]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'region', targetEntity: EvenementRegion::class, cascade: ['persist', 'remove'])]
    private Collection $evenementRegions;

    public function __construct()
    {
        $this->evenementRegions = new ArrayCollection();
    }

    public function getEvenementRegions(): Collection
    {
        return $this->evenementRegions;
    }

    public function addEvenementRegion(EvenementRegion $evenementRegion): self
    {
        if (!$this->evenementRegions->contains($evenementRegion)) {
            $this->evenementRegions->add($evenementRegion);
            $evenementRegion->setRegion($this);
        }

        return $this;
    }

    public function removeEvenementRegion(EvenementRegion $evenementRegion): self
    {
        if ($this->evenementRegions->removeElement($evenementRegion)) {
            if ($evenementRegion->getRegion() === $this) {
                $evenementRegion->setRegion(null);
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

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
}
