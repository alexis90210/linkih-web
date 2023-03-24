<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?int $montant = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'Abonnement', targetEntity: AbonnementVendeur::class)]
    private Collection $abonnementVendeurs;

    #[ORM\Column(length: 255)]
    private ?int $duree_abonnement = null;

    #[ORM\Column(length: 255)]
    private ?string $devise = null;

    #[ORM\Column(length: 255)]
    private ?string $type_abonnement = null;

    #[ORM\Column(length: 255)]
    private ?string $EnNom = null;

    public function __construct()
    {
        $this->abonnementVendeurs = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, AbonnementVendeur>
     */
    public function getAbonnementVendeurs(): Collection
    {
        return $this->abonnementVendeurs;
    }

    public function addAbonnementVendeur(AbonnementVendeur $abonnementVendeur): self
    {
        if (!$this->abonnementVendeurs->contains($abonnementVendeur)) {
            $this->abonnementVendeurs->add($abonnementVendeur);
            $abonnementVendeur->setAbonnement($this);
        }

        return $this;
    }

    public function removeAbonnementVendeur(AbonnementVendeur $abonnementVendeur): self
    {
        if ($this->abonnementVendeurs->removeElement($abonnementVendeur)) {
            // set the owning side to null (unless already changed)
            if ($abonnementVendeur->getAbonnement() === $this) {
                $abonnementVendeur->setAbonnement(null);
            }
        }

        return $this;
    }

    public function getDureeAbonnement(): ?int
    {
        return $this->duree_abonnement;
    }

    public function setDureeAbonnement(int $duree_abonnement): self
    {
        $this->duree_abonnement = $duree_abonnement;

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

    public function getTypeAbonnement(): ?string
    {
        return $this->type_abonnement;
    }

    public function setTypeAbonnement(string $type_abonnement): self
    {
        $this->type_abonnement = $type_abonnement;

        return $this;
    }

    public function getEnNom(): ?string
    {
        return $this->EnNom;
    }

    public function setEnNom(string $EnNom): self
    {
        $this->EnNom = $EnNom;

        return $this;
    }
}
