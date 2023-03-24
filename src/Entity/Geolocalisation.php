<?php

namespace App\Entity;

use App\Repository\GeolocalisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GeolocalisationRepository::class)]
class Geolocalisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Longitude = null;

    #[ORM\Column(length: 255)]
    private ?string $Latitude = null;

    #[ORM\OneToMany(mappedBy: 'geolocalisation', targetEntity: Utilisateurs::class)]
    private Collection $Utilisateur;

    #[ORM\OneToMany(mappedBy: 'geolocalisation', targetEntity: Vendeur::class)]
    private Collection $Vendeur;

    public function __construct()
    {
        $this->Utilisateur = new ArrayCollection();
        $this->Vendeur = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLongitude(): ?string
    {
        return $this->Longitude;
    }

    public function setLongitude(string $Longitude): self
    {
        $this->Longitude = $Longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->Latitude;
    }

    public function setLatitude(string $Latitude): self
    {
        $this->Latitude = $Latitude;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateurs>
     */
    public function getUtilisateur(): Collection
    {
        return $this->Utilisateur;
    }

    public function addUtilisateur(Utilisateurs $utilisateur): self
    {
        if (!$this->Utilisateur->contains($utilisateur)) {
            $this->Utilisateur->add($utilisateur);
            $utilisateur->setGeolocalisation($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateurs $utilisateur): self
    {
        if ($this->Utilisateur->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getGeolocalisation() === $this) {
                $utilisateur->setGeolocalisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vendeur>
     */
    public function getVendeur(): Collection
    {
        return $this->Vendeur;
    }

    public function addVendeur(Vendeur $vendeur): self
    {
        if (!$this->Vendeur->contains($vendeur)) {
            $this->Vendeur->add($vendeur);
            $vendeur->setGeolocalisation($this);
        }

        return $this;
    }

    public function removeVendeur(Vendeur $vendeur): self
    {
        if ($this->Vendeur->removeElement($vendeur)) {
            // set the owning side to null (unless already changed)
            if ($vendeur->getGeolocalisation() === $this) {
                $vendeur->setGeolocalisation(null);
            }
        }

        return $this;
    }
}
