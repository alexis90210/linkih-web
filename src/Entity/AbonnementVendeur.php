<?php

namespace App\Entity;

use App\Repository\AbonnementVendeurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementVendeurRepository::class)]
class AbonnementVendeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'abonnementVendeur', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Vendeur $Vendeur = null;

    #[ORM\Column(length: 255)]
    private ?string $expiration = null;

    #[ORM\ManyToOne(inversedBy: 'abonnementVendeurs')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Abonnement $Abonnement = null;

    #[ORM\Column(length: 255)]
    private ?string $date_activation = null;

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

    public function getExpiration(): ?string
    {
        return $this->expiration;
    }

    public function setExpiration(string $expiration): self
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function getAbonnement(): ?Abonnement
    {
        return $this->Abonnement;
    }

    public function setAbonnement(?Abonnement $Abonnement): self
    {
        $this->Abonnement = $Abonnement;

        return $this;
    }

    public function getDateActivation(): ?string
    {
        return $this->date_activation;
    }

    public function setDateActivation(string $date_activation): self
    {
        $this->date_activation = $date_activation;

        return $this;
    }
}
