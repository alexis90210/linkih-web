<?php

namespace App\Entity;

use App\Repository\PrestationsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestationsRepository::class)]
class Prestations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Prix = null;

    #[ORM\Column(length: 255)]
    private ?string $Duree = null;

    #[ORM\Column(length: 255)]
    private ?string $produit = null;

    #[ORM\ManyToOne(inversedBy: 'prestations')]
    private ?SousCategorie $SousCategorie = null;

    #[ORM\ManyToOne(inversedBy: 'prestations')]
    private ?Vendeur $Vendeur = null;

    #[ORM\Column(length: 255)]
    private ?string $devise = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?string
    {
        return $this->Prix;
    }

    public function setPrix(string $Prix): self
    {
        $this->Prix = $Prix;

        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->Duree;
    }

    public function setDuree(string $Duree): self
    {
        $this->Duree = $Duree;

        return $this;
    }

    public function getProduit(): ?string
    {
        return $this->produit;
    }

    public function setProduit(string $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getSousCategorie(): ?SousCategorie
    {
        return $this->SousCategorie;
    }

    public function setSousCategorie(?SousCategorie $SousCategorie): self
    {
        $this->SousCategorie = $SousCategorie;

        return $this;
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

    public function getDevise(): ?string
    {
        return $this->devise;
    }

    public function setDevise(string $devise): self
    {
        $this->devise = $devise;

        return $this;
    }
}
