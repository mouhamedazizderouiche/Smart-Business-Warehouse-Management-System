<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[Vich\Uploadable] // Add this attribute
class Produit
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $nom;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix unitaire est obligatoire.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    private float $prixUnitaire;

    #[Vich\UploadableField(mapping: 'product_image', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateCreation;

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: 'produits')]
    #[ORM\JoinColumn(name: 'categorie_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Categorie $categorie = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "La quantité est obligatoire.")]
    #[Assert\PositiveOrZero(message: "La quantité ne peut pas être négative.")]
    private int $quantite;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'produit')]
    private Collection $stocks;
    
    
    public function getStocks(): Collection
    {
        return $this->stocks;
    }
    
    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setProduit($this);
        }
        return $this;
    }
    
    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            if ($stock->getProduit() === $this) {
                $stock->setProduit(null);
            }
        }
        return $this;
    }
    #[ORM\Column(type: "decimal", precision: 2, scale: 1, nullable: true)]
    private ?float $rate = null;


    // One-to-Many relationship with Commentaire
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'produit', cascade: ['persist', 'remove'])]
    private $commentaires;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->dateCreation = new \DateTime();
        $this->stocks = new ArrayCollection();

        $this->commentaires = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getPrixUnitaire(): float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(float $prixUnitaire): self
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->dateCreation = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }
    public function increaseQuantite(int $amount = 1): self
    {
        $this->quantite += $amount;
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

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(?float $rate): self
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|\App\Entity\Commentaire[]
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setProduit($this);
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // Set the owning side to null (unless already changed)
            if ($commentaire->getProduit() === $this) {
                $commentaire->setProduit(null);
            }
        }
        return $this;
    }

    public function afficherDetails(): string
    {
        return "Produit: {$this->nom}, Prix: {$this->prixUnitaire} TND";
    }
   
    public function diminuerQuantite(int $quantite): bool
{
    if ($this->quantite >= $quantite) {
        $this->quantite -= $quantite;
        return true;
    }
    return false; // Quantité insuffisante
}

public function _toString(): string
{
    return $this->nom;
}

    public function __toString(): string
    {
        return sprintf(
            $this->nom,
            $this->description ?? 'Aucune description',
            $this->prixUnitaire,
            $this->quantite,
            $this->categorie ? $this->categorie->getNom() : 'Non spécifiée',
            $this->dateCreation->format('Y-m-d H:i:s'),
        );
    }
}
