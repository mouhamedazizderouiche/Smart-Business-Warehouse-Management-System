<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

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

    #[ORM\ManyToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Commande $commande = null; // 🔥 Ajout de la relation avec Commande

        // ✅ AJOUT DE LA RELATION USER
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: false)]
        private ?User $user = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->dateCommande = new \DateTime();
    }

    public function getId(): ?Uuid { return $this->id; }
    public function getNomProduit(): string { return $this->nomProduit; }
    public function setNomProduit(string $nomProduit): self { $this->nomProduit = $nomProduit; return $this; }
    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $quantite): self { $this->quantite = $quantite; return $this; }
    public function getPrixTotal(): float { return $this->prixTotal; }
    public function setPrixTotal(float $prixTotal): self { $this->prixTotal = $prixTotal; return $this; }
    public function getDateCommande(): \DateTimeInterface { return $this->dateCommande; }
    public function getCommande(): ?Commande { return $this->commande; }
    public function setCommande(?Commande $commande): self { $this->commande = $commande; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
}
