<?php

namespace App\Entity;

use App\Repository\FournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
class Fournisseur
{
    

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $actif = null;

    /**
     * @var Collection<int, stock>
     */
    #[ORM\ManyToMany(targetEntity: stock::class, inversedBy: 'id_fournisseur')]
    private Collection $stocks;

    #[ORM\Column(type: 'uuid')]
    #[ORM\Id]
    #[ORM\CustomIdGenerator(class: 'Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator')]
    private ?Uuid $id = null;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getActif(): ?string
    {
        return $this->actif;
    }

    public function setActif(?string $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * @return Collection<int, stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(stock $stock): static
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
        }

        return $this;
    }

    public function removeStock(stock $stock): static
    {
        $this->stocks->removeElement($stock);

        return $this;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }
}
