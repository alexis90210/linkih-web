<?php

namespace App\Entity;

use App\Repository\LienReseauxSociauxRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LienReseauxSociauxRepository::class)]
class LienReseauxSociaux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'lienReseauxSociauxes')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Vendeur $Vendeur = null;

    #[ORM\Column(length: 255)]
    private ?string $Username = null;

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


    public function getVendeur(): ?Vendeur
    {
        return $this->Vendeur;
    }

    public function setVendeur(?Vendeur $Vendeur): self
    {
        $this->Vendeur = $Vendeur;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->Username;
    }

    public function setUsername(string $Username): self
    {
        $this->Username = $Username;

        return $this;
    }
}
