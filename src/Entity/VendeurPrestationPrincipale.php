<?php

namespace App\Entity;

use App\Repository\VendeurPrestationPrincipaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VendeurPrestationPrincipaleRepository::class)]
class VendeurPrestationPrincipale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vendeurPrestationPrincipales')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Vendeur $Vendeur = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\OneToMany(mappedBy: 'VendeurPrestationPrincipale', targetEntity: VendeurSousPrestation::class)]
    private Collection $vendeurSousPrestations;

    public function __construct()
    {
        $this->vendeurSousPrestations = new ArrayCollection();
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

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): self
    {
        $this->Nom = $Nom;

        return $this;
    }

    /**
     * @return Collection<int, VendeurSousPrestation>
     */
    public function getVendeurSousPrestations(): Collection
    {
        return $this->vendeurSousPrestations;
    }

    public function addVendeurSousPrestation(VendeurSousPrestation $vendeurSousPrestation): self
    {
        if (!$this->vendeurSousPrestations->contains($vendeurSousPrestation)) {
            $this->vendeurSousPrestations->add($vendeurSousPrestation);
            $vendeurSousPrestation->setVendeurPrestationPrincipale($this);
        }

        return $this;
    }

    public function removeVendeurSousPrestation(VendeurSousPrestation $vendeurSousPrestation): self
    {
        if ($this->vendeurSousPrestations->removeElement($vendeurSousPrestation)) {
            // set the owning side to null (unless already changed)
            if ($vendeurSousPrestation->getVendeurPrestationPrincipale() === $this) {
                $vendeurSousPrestation->setVendeurPrestationPrincipale(null);
            }
        }

        return $this;
    }
}
