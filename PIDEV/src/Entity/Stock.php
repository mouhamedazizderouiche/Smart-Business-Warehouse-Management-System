<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
  #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateMiseAjour = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAjout;

    #[ORM\ManyToOne(targetEntity: Entrepot::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entrepot $entrepot = null;

    #[ORM\ManyToMany(targetEntity: Fournisseur::class, inversedBy: 'stocks')]
    private Collection $fournisseurs;

    public function __construct()
    {
        $this->fournisseurs = new ArrayCollection();
        $this->dateAjout = new \DateTime(); // Initialisation automatique
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getDateMiseAjour(): ?\DateTimeInterface
    {
        return $this->dateMiseAjour;
    }

    public function setDateMiseAjour(\DateTimeInterface $dateMiseAjour): static
    {
        $this->dateMiseAjour = $dateMiseAjour;
        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): static
    {
        $this->dateAjout = $dateAjout;
        return $this;
    }

    public function getEntrepot(): ?Entrepot
    {
        return $this->entrepot;
    }

    public function setEntrepot(?Entrepot $entrepot): static
    {
        $this->entrepot = $entrepot;
        return $this;
    }

    public function getFournisseurs(): Collection
    {
        return $this->fournisseurs;
    }

    public function addFournisseur(Fournisseur $fournisseur): static
    {
        if (!$this->fournisseurs->contains($fournisseur)) {
            $this->fournisseurs->add($fournisseur);
        }
        return $this;
    }

    public function removeFournisseur(Fournisseur $fournisseur): static
    {
        $this->fournisseurs->removeElement($fournisseur);
        return $this;
    }
}
