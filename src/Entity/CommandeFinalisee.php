<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use App\Repository\CommandeFinaliseeRepository;
use App\Entity\User;

#[ORM\Entity]
class CommandeFinalisee
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $nomProduit;

    #[ORM\Column(type: "integer")]
    private int $quantite;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private float $prixTotal;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateCommande;

    #[ORM\Column(type: "uuid")]
    private ?Uuid $produitId = null;
    
    
    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private float $produitPrix;
    

        
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: false)]
        private ?User $user = null;

        public function __construct()
        {
            $this->id = Uuid::v4();
            $this->dateCommande = new \DateTime();
            $this->produitId = null;
            $this->produitNom = '';
            $this->produitPrix = 0.0;
        }
        

    public function getId(): ?Uuid { return $this->id; }
    public function getNomProduit(): string { return $this->nomProduit; }
    public function setNomProduit(string $nomProduit): self { $this->nomProduit = $nomProduit; return $this; }
    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $quantite): self { $this->quantite = $quantite; return $this; }
    public function getPrixTotal(): float { return $this->prixTotal; }
    public function setPrixTotal(float $prixTotal): self { $this->prixTotal = $prixTotal; return $this; }
    public function setDateCommande(\DateTimeInterface $dateCommande): self 
{
    $this->dateCommande = $dateCommande;
    return $this;
}

    public function getDateCommande(): \DateTimeInterface { return $this->dateCommande; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getProduitId(): ?Uuid { return $this->produitId; }
public function setProduitId(?Uuid $produitId): self { $this->produitId = $produitId; return $this; }



public function getProduitPrix(): float { return $this->produitPrix; }
public function setProduitPrix(float $produitPrix): self { $this->produitPrix = $produitPrix; return $this; }

}
