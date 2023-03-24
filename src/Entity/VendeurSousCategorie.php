<?php

namespace App\Entity;

use App\Repository\VendeurSousCategorieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VendeurSousCategorieRepository::class)]
class VendeurSousCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendeur::class, inversedBy: 'vendeurSousCategories')]
    //#[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Vendeur $Vendeur = null;

    #[ORM\Column(length: 255)]
    private ?string $sousCategorie = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendeur(): ?Vendeur
    {
        return $this->Vendeur;
    }

    public function setVendeur(?Vendeur $Vendeur): self
    {
        $this->Vendeur = $Vendeur;

        return $this;
    }

    public function getSousCategorie(): ?string
    {
        return $this->sousCategorie;
    }

    public function setSousCategorie(string $sousCategorie): self
    {
        $this->sousCategorie = $sousCategorie;

        return $this;
    }
}
