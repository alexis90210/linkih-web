<?php

namespace App\Entity;

use App\Repository\GallerieVendeurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GallerieVendeurRepository::class)]
class GallerieVendeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'gallerieVendeurs')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Vendeur $Vendeur = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Image = null;

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

    public function getImage(): ?string
    {
        return $this->Image;
    }

    public function setImage(string $Image): self
    {
        $this->Image = $Image;

        return $this;
    }
}
