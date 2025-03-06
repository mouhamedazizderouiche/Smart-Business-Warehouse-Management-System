<?php

namespace App\Entity;

use App\Repository\StockRepository;
use App\Validator\Constraints\DateEntreeConstraint;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[DateEntreeConstraint]
    #[Assert\NotBlank(message: 'La date d\'entrée est obligatoire.')]
    private ?\DateTimeInterface $date_entree = null;
    

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: 'La date sortie doit étre superieur au date d\'entrée.')]

    private ?\DateTimeInterface $date_sortie = null;

    #[ORM\ManyToMany(targetEntity: Fournisseur::class, mappedBy: 'stocks')]
    #[Assert\Count(min: 1, minMessage: 'Vous devez sélectionner au moins un fournisseur.')]
    private Collection $fournisseurs;

    #[ORM\ManyToMany(targetEntity: Entrepot::class, inversedBy: 'stocks')]
    #[ORM\JoinTable(name: 'stock_entrepot')]
    #[Assert\Count(min: 1, minMessage: 'Vous devez sélectionner au moins un entrepôt.')]
    private Collection $entrepots;

    #[ORM\Column]
    private ?int $seuil_alert = null;


    public function __construct()
    {
        $this->fournisseurs = new ArrayCollection();
        $this->entrepots = new ArrayCollection();
    }
    public function toArray(): array
{
    return [
        'product_id' => $this->getProduit()->getId(),
        'quantity' => $this->getSeuilAlert(), // Ou une autre colonne représentant la quantité
        'date' => $this->getDateEntree()->format('Y-m-d'),
    ];
}

    public function getId(): ?string
    {
        return $this->id?->toRfc4122();
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }

    

    public function getDateEntree(): ?\DateTimeInterface
    {
        return $this->date_entree;
    }

    public function setDateEntree(\DateTimeInterface $date_entree): self
    {
        $this->date_entree = $date_entree;
        return $this;
    }

    public function getDateSortie(): ?\DateTimeInterface
    {
        return $this->date_sortie;
    }

    public function setDateSortie(?\DateTimeInterface $date_sortie): self
    {
        $this->date_sortie = $date_sortie;
        return $this;
    }

    public function getFournisseurs(): Collection
    {
        return $this->fournisseurs;
    }

    public function addFournisseur(Fournisseur $fournisseur): self
    {
        if (!$this->fournisseurs->contains($fournisseur)) {
            $this->fournisseurs->add($fournisseur);
            $fournisseur->addStock($this);
        }
        return $this;
    }

    public function removeFournisseur(Fournisseur $fournisseur): self
    {
        if ($this->fournisseurs->removeElement($fournisseur)) {
            $fournisseur->removeStock($this);
        }
        return $this;
    }

    public function getEntrepots(): Collection
    {
        return $this->entrepots;
    }

    public function addEntrepot(Entrepot $entrepot): static
    {
        if (!$this->entrepots->contains($entrepot)) {
            $this->entrepots->add($entrepot);
            $entrepot->addStock($this);
        }

        return $this;
    }


    public function removeEntrepot(Entrepot $entrepot): static
    {
        if ($this->entrepots->removeElement($entrepot)) {
            $entrepot->removeStock($this);
        }

        return $this;
    }

    public function getPrixProduit(): float
{
    return $this->produit ? $this->produit->getPrixUnitaire() : 0;
}

    public function getSeuilAlert(): ?int
    {
        return $this->seuil_alert;
    }

    public function setSeuilAlert(int $seuil_alert): static
    {
        $this->seuil_alert = $seuil_alert;

        return $this;
    }

  }