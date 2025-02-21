<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class Categorie
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $imgUrl = null;

    #[ORM\Column(type: "string", length: 255, unique: true, nullable: true)]
    private ?string $slug = null;


    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: "subcategories")]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: "parent")]
    private Collection $subcategories;

    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: "categorie", cascade: ["persist", "remove"])]
    private Collection $produits;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->subcategories = new ArrayCollection();
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
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

    public function getImgUrl(): ?string
    {
        return $this->imgUrl;
    }

    public function setImgUrl(?string $imgUrl): self
    {
        $this->imgUrl = $imgUrl;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function getSubcategories(): Collection
    {
        return $this->subcategories;
    }

    public function addSubcategory(self $subcategory): self
    {
        if (!$this->subcategories->contains($subcategory)) {
            $this->subcategories[] = $subcategory;
            $subcategory->setParent($this);
        }
        return $this;
    }

    public function removeSubcategory(self $subcategory): self
    {
        if ($this->subcategories->removeElement($subcategory)) {
            if ($subcategory->getParent() === $this) {
                $subcategory->setParent(null);
            }
        }
        return $this;
    }

    public function ajouterProduit(Produit $produit): void
    {
        if (!$this->produits->contains($produit)) {
            $this->produits[] = $produit;
            $produit->setCategorie($this);
        }
    }

    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}