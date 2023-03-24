<?php

namespace App\Entity;

use App\Repository\VendeurSousPrestationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VendeurSousPrestationRepository::class)]
class VendeurSousPrestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Titre = null;

    #[ORM\Column(length: 255)]
    private ?string $SousTitre = null;

    #[ORM\Column(length: 255)]
    private ?string $Prix = null;

    #[ORM\Column(length: 255)]
    private ?string $Duree = null;

    #[ORM\ManyToOne(inversedBy: 'vendeurSousPrestations')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?VendeurPrestationPrincipale $VendeurPrestationPrincipale = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(string $Titre): self
    {
        $this->Titre = $Titre;

        return $this;
    }

    public function getSousTitre(): ?string
    {
        return $this->SousTitre;
    }

    public function setSousTitre(string $SousTitre): self
    {
        $this->SousTitre = $SousTitre;

        return $this;
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

    public function getVendeurPrestationPrincipale(): ?VendeurPrestationPrincipale
    {
        return $this->VendeurPrestationPrincipale;
    }

    public function setVendeurPrestationPrincipale(?VendeurPrestationPrincipale $VendeurPrestationPrincipale): self
    {
        $this->VendeurPrestationPrincipale = $VendeurPrestationPrincipale;

        return $this;
    }
}
