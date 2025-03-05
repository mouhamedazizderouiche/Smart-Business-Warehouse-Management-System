<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: 'App\Repository\CategorieRepository')]
#[Gedmo\Tree(type: 'nested')]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[Gedmo\Slug(fields: ['nom'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private $slug;

    #[Gedmo\TreeLeft]
    #[ORM\Column(type: 'integer', nullable: true)]
    private $lft;

    #[Gedmo\TreeRight]
    #[ORM\Column(type: 'integer', nullable: true)]
    private $rgt;

    #[Gedmo\TreeLevel]
    #[ORM\Column(type: 'integer', nullable: true)]
    private $lvl;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Categorie', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $parent;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Categorie', mappedBy: 'parent', cascade: ['remove'])]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private $children;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $imgUrl;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $description;

    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'categorie', cascade: ['remove'])]
    private $produits;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description= $description;
        return $this;
    }


    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getLft(): ?int
    {
        return $this->lft;
    }

    public function getRgt(): ?int
    {
        return $this->rgt;
    }

    public function getLvl(): ?int
    {
        return $this->lvl;
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

    public function getChildren(): \Doctrine\Common\Collections\Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
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
}