<?php

namespace App\Entity;

use App\Repository\EntrepotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\ManyToOne(targetEntity: Stock::class, inversedBy: 'entrepots')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Stock $stock = null;

    #[ORM\Column]
    private ?float $espace = null;

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

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): static
    {
        $this->stock = $stock;
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
}
