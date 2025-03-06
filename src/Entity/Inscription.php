<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\InscriptionRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'Veuillez entrer un email valide.')]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est obligatoire.')]
    private ?string $num_tel = null;

    // Renamed the 'role' field to 'occupation' or 'travail'
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Veuillez choisir un travail.')]
    private ?string $travail = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evenement $evenement = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $dateInscription = null;

    public function __construct()
    {
        $this->dateInscription = new \DateTime();
    }

    // Getters et setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getNumTel(): ?string
    {
        return $this->num_tel;
    }

    public function setNumTel(?string $num_tel): self
    {
        $this->num_tel = $num_tel;
        return $this;
    }

    public function getTravail(): ?string
    {
        return $this->travail;
    }

    public function setTravail(string $travail): self
    {
        $this->travail = $travail;
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

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): self
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }
}
