<?php

namespace App\Entity;

use App\Repository\EntrepotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: EntrepotRepository::class)]
#[UniqueEntity(fields: ['nom'], message: 'Un entrepôt avec ce nom existe déjà.')]
class Entrepot
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le nom de l\'entrepôt ne peut pas être vide.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse ne peut pas être vide.')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'L\'adresse doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: 'La ville ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $ville = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'L\'espace ne peut pas être vide.')]
    #[Assert\Positive(message: 'L\'espace doit être un nombre positif.')]
    private ?float $espace = null;


    #[ORM\ManyToMany(targetEntity: Stock::class, mappedBy: 'entrepots')]
    private Collection $stocks;

    
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->addEntrepot($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            $stock->removeEntrepot($this);
        }

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id?->toRfc4122();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getEspace(): ?float
    {
        return $this->espace;
    }

    public function setEspace(float $espace): static
    {
        $this->espace = $espace;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }
    
}
