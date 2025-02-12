<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $prixUnitaire = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $urlImageProduit;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCréation = null;

    /**
     * @var Collection<int, Fournisseur>
     */
    #[ORM\ManyToMany(targetEntity: Fournisseur::class, inversedBy: 'id_produit')]
    private Collection $id_fournisseur;

    public function __construct()
    {
        $this->id_fournisseur = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrixUnitaire(): ?string
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(string $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }

    public function getUrlImageProduit()
    {
        return $this->urlImageProduit;
    }

    public function setUrlImageProduit($urlImageProduit): static
    {
        $this->urlImageProduit = $urlImageProduit;

        return $this;
    }

    public function getDateCréation(): ?\DateTimeInterface
    {
        return $this->dateCréation;
    }

    public function setDateCréation(\DateTimeInterface $dateCréation): static
    {
        $this->dateCréation = $dateCréation;

        return $this;
    }

    /**
     * @return Collection<int, Fournisseur>
     */
    public function getIdFournisseur(): Collection
    {
        return $this->id_fournisseur;
    }

    public function addIdFournisseur(Fournisseur $idFournisseur): static
    {
        if (!$this->id_fournisseur->contains($idFournisseur)) {
            $this->id_fournisseur->add($idFournisseur);
        }

        return $this;
    }

    public function removeIdFournisseur(Fournisseur $idFournisseur): static
    {
        $this->id_fournisseur->removeElement($idFournisseur);

        return $this;
    }
}
