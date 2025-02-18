<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\DateEntreeConstraint;

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
    #[Assert\NotBlank(message: 'Le produit est obligatoire.')]
    private ?Produit $produit = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\Positive(message: 'La quantité doit être un nombre positif.')]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[DateEntreeConstraint]
    #[Assert\NotBlank(message: 'La date d\'entrée est obligatoire.')]
    private ?\DateTimeInterface $date_entree = null;
    

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_sortie = null;

    #[ORM\ManyToMany(targetEntity: Fournisseur::class, mappedBy: 'stocks')]
    #[Assert\Count(min: 1, minMessage: 'Vous devez sélectionner au moins un fournisseur.')]
    private Collection $fournisseurs;

    #[ORM\OneToMany(targetEntity: Entrepot::class, mappedBy: 'stock', cascade: ['persist', 'remove'])]
    #[Assert\Count(min: 1, minMessage: 'Vous devez sélectionner au moins un entrepôt.')]
    private Collection $entrepots;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->fournisseurs = new ArrayCollection();
        $this->entrepots = new ArrayCollection();
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

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
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

    public function addEntrepot(Entrepot $entrepot): self
{
    if (!$this->entrepots->contains($entrepot)) {
        $this->entrepots->add($entrepot);
        $this->entrepots[] = $entrepot;
        $entrepot->setStock($this);
    }
    return $this;
}

public function removeEntrepot(Entrepot $entrepot): self
{
    if ($this->entrepots->removeElement($entrepot)) {
      if ($entrepot->getStock() === $this) {
        $entrepot->setStock(null); // Dissocier l'entrepôt du stock
    }
  }
    return $this;
}


    public function getPrixProduit(): float
{
    return $this->produit ? $this->produit->getPrixUnitaire() : 0;
}

}
