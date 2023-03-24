<?php

namespace App\Entity;

use App\Repository\VendeurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VendeurRepository::class)]
class Vendeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $mail = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $mobile = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $CorpsMetier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $StripeAccountId = null;

    #[ORM\OneToMany(mappedBy: 'Vendeur', targetEntity: LienReseauxSociaux::class)]
    private Collection $lienReseauxSociauxes;

    #[ORM\OneToMany(mappedBy: 'Vendeur', targetEntity: HoraireOuverture::class)]
    private Collection $horaireOuvertures;

    #[ORM\ManyToOne(inversedBy: 'vendeurs')]
    private ?Utilisateurs $Utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'Vendeur', targetEntity: RendezVous::class)]
    private Collection $rendezVouses;

    #[ORM\OneToOne(mappedBy: 'Vendeur', cascade: ['persist', 'remove'])]
    private ?AbonnementVendeur $abonnementVendeur = null;

    #[ORM\ManyToOne(inversedBy: 'Vendeur')]
    private ?Geolocalisation $geolocalisation = null;

    #[ORM\Column]
    private array $Categorie = [];

    #[ORM\Column(length: 255)]
    private ?string $NomResponsable = null;

    #[ORM\Column(length: 255)]
    private ?string $Sciem = null;

    #[ORM\Column(length: 255)]
    private ?string $PosteResponsable = null;

    #[ORM\Column]
    private ?int $CompteActif = null;

    #[ORM\Column]
    private ?int $CompteConfirme = null;

    #[ORM\Column(length: 255)]
    private ?string $CodeConfirmation = null;

    #[ORM\OneToMany(mappedBy: 'Vendeur', targetEntity: VendeurSousCategorie::class)]
    private Collection $vendeurSousCategories;

    #[ORM\OneToMany(mappedBy: 'Vendeur', targetEntity: VendeurPrestationPrincipale::class)]
    private Collection $vendeurPrestationPrincipales;

    #[ORM\OneToMany(mappedBy: 'Vendeur', targetEntity: VendeurNote::class)]
    private Collection $vendeurNotes;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Logo = null;

    #[ORM\OneToMany(mappedBy: 'Vendeur', targetEntity: Prestations::class)]
    private Collection $prestations;

    #[ORM\OneToMany(mappedBy: 'Vendeur', targetEntity: GallerieVendeur::class)]
    private Collection $gallerieVendeurs;

    #[ORM\Column(length: 255)]
    private ?string $type_etablissement = null;


    public function __construct()
    {
        $this->lienReseauxSociauxes = new ArrayCollection();
        $this->horaireOuvertures = new ArrayCollection();
        $this->rendezVouses = new ArrayCollection();
        $this->vendeurSousCategories = new ArrayCollection();
        $this->vendeurPrestationPrincipales = new ArrayCollection();
        $this->vendeurNotes = new ArrayCollection();
        $this->prestations = new ArrayCollection();
        $this->gallerieVendeurs = new ArrayCollection();
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

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCorpsMetier(): ?string
    {
        return $this->CorpsMetier;
    }

    public function setCorpsMetier(string $CorpsMetier): self
    {
        $this->CorpsMetier = $CorpsMetier;

        return $this;
    }

    /**
     * @return Collection<int, LienReseauxSociaux>
     */
    public function getLienReseauxSociauxes(): Collection
    {
        return $this->lienReseauxSociauxes;
    }

    public function addLienReseauxSociaux(LienReseauxSociaux $lienReseauxSociaux): self
    {
        if (!$this->lienReseauxSociauxes->contains($lienReseauxSociaux)) {
            $this->lienReseauxSociauxes->add($lienReseauxSociaux);
            $lienReseauxSociaux->setVendeur($this);
        }

        return $this;
    }

    public function removeLienReseauxSociaux(LienReseauxSociaux $lienReseauxSociaux): self
    {
        if ($this->lienReseauxSociauxes->removeElement($lienReseauxSociaux)) {
            // set the owning side to null (unless already changed)
            if ($lienReseauxSociaux->getVendeur() === $this) {
                $lienReseauxSociaux->setVendeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HoraireOuverture>
     */
    public function getHoraireOuvertures(): Collection
    {
        return $this->horaireOuvertures;
    }

    public function addHoraireOuverture(HoraireOuverture $horaireOuverture): self
    {
        if (!$this->horaireOuvertures->contains($horaireOuverture)) {
            $this->horaireOuvertures->add($horaireOuverture);
            $horaireOuverture->setVendeur($this);
        }

        return $this;
    }

    public function removeHoraireOuverture(HoraireOuverture $horaireOuverture): self
    {
        if ($this->horaireOuvertures->removeElement($horaireOuverture)) {
            // set the owning side to null (unless already changed)
            if ($horaireOuverture->getVendeur() === $this) {
                $horaireOuverture->setVendeur(null);
            }
        }
        return $this;
    }

    public function getUtilisateur(): ?Utilisateurs
    {
        return $this->Utilisateur;
    }

    public function setUtilisateur(?Utilisateurs $Utilisateur): self
    {
        $this->Utilisateur = $Utilisateur;

        return $this;
    }



    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVouses(): Collection
    {
        return $this->rendezVouses;
    }

    public function addRendezVouse(RendezVous $rendezVouse): self
    {
        if (!$this->rendezVouses->contains($rendezVouse)) {
            $this->rendezVouses->add($rendezVouse);
            $rendezVouse->setVendeur($this);
        }

        return $this;
    }

    public function removeRendezVouse(RendezVous $rendezVouse): self
    {
        if ($this->rendezVouses->removeElement($rendezVouse)) {
            // set the owning side to null (unless already changed)
            if ($rendezVouse->getVendeur() === $this) {
                $rendezVouse->setVendeur(null);
            }
        }

        return $this;
    }

    public function getAbonnementVendeur(): ?AbonnementVendeur
    {
        return $this->abonnementVendeur;
    }

    public function setAbonnementVendeur(?AbonnementVendeur $abonnementVendeur): self
    {
        // unset the owning side of the relation if necessary
        if ($abonnementVendeur === null && $this->abonnementVendeur !== null) {
            $this->abonnementVendeur->setVendeur(null);
        }

        // set the owning side of the relation if necessary
        if ($abonnementVendeur !== null && $abonnementVendeur->getVendeur() !== $this) {
            $abonnementVendeur->setVendeur($this);
        }

        $this->abonnementVendeur = $abonnementVendeur;

        return $this;
    }

    public function getGeolocalisation(): ?Geolocalisation
    {
        return $this->geolocalisation;
    }

    public function setGeolocalisation(?Geolocalisation $geolocalisation): self
    {
        $this->geolocalisation = $geolocalisation;

        return $this;
    }

    public function getCategorie(): array
    {
        return $this->Categorie;
    }

    public function setCategorie(array $Categorie): self
    {
        $this->Categorie = $Categorie;

        return $this;
    }

    public function getNomResponsable(): ?string
    {
        return $this->NomResponsable;
    }

    public function setNomResponsable(string $NomResponsable): self
    {
        $this->NomResponsable = $NomResponsable;

        return $this;
    }

    public function getSciem(): ?string
    {
        return $this->Sciem;
    }

    public function setSciem(string $Sciem): self
    {
        $this->Sciem = $Sciem;

        return $this;
    }

    public function getPosteResponsable(): ?string
    {
        return $this->PosteResponsable;
    }

    public function setPosteResponsable(string $PosteResponsable): self
    {
        $this->PosteResponsable = $PosteResponsable;

        return $this;
    }

    public function getCompteActif(): ?int
    {
        return $this->CompteActif;
    }

    public function setCompteActif(int $CompteActif): self
    {
        $this->CompteActif = $CompteActif;

        return $this;
    }

    public function getCompteConfirme(): ?int
    {
        return $this->CompteConfirme;
    }

    public function setCompteConfirme(int $CompteConfirme): self
    {
        $this->CompteConfirme = $CompteConfirme;

        return $this;
    }

    public function getCodeConfirmation(): ?string
    {
        return $this->CodeConfirmation;
    }

    public function setCodeConfirmation(string $CodeConfirmation): self
    {
        $this->CodeConfirmation = $CodeConfirmation;

        return $this;
    }


    /**
     * @return Collection<int, VendeurSousCategorie>
     */
    public function getVendeurSousCategories(): Collection
    {
        return $this->vendeurSousCategories;
    }



    /**
     * @return Collection<int, VendeurPrestationPrincipale>
     */
    public function getVendeurPrestationPrincipales(): Collection
    {
        return $this->vendeurPrestationPrincipales;
    }


    /**
     * @return Collection<int, VendeurNote>
     */
    public function getVendeurNotes(): Collection
    {
        return $this->vendeurNotes;
    }

    public function addVendeurNote(VendeurNote $vendeurNote): self
    {
        if (!$this->vendeurNotes->contains($vendeurNote)) {
            $this->vendeurNotes->add($vendeurNote);
            $vendeurNote->setVendeur($this);
        }

        return $this;
    }

    public function removeVendeurNote(VendeurNote $vendeurNote): self
    {
        if ($this->vendeurNotes->removeElement($vendeurNote)) {
            // set the owning side to null (unless already changed)
            if ($vendeurNote->getVendeur() === $this) {
                $vendeurNote->setVendeur(null);
            }
        }

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->Logo;
    }

    public function setLogo(string $Logo): self
    {
        $this->Logo = $Logo;

        return $this;
    }

    /**
     * @return Collection<int, Prestations>
     */
    public function getPrestations(): Collection
    {
        return $this->prestations;
    }

    public function addPrestation(Prestations $prestation): self
    {
        if (!$this->prestations->contains($prestation)) {
            $this->prestations->add($prestation);
            $prestation->setVendeur($this);
        }

        return $this;
    }

    public function removePrestation(Prestations $prestation): self
    {
        if ($this->prestations->removeElement($prestation)) {
            // set the owning side to null (unless already changed)
            if ($prestation->getVendeur() === $this) {
                $prestation->setVendeur(null);
            }
        }

        return $this;
    }

    public function getStripeAccountId(): ?string
    {
        return $this->StripeAccountId;
    }

    public function setStripeAccountId(?string $StripeAccountId): self
    {
        $this->StripeAccountId = $StripeAccountId;

        return $this;
    }

    /**
     * @return Collection<int, GallerieVendeur>
     */
    public function getGallerieVendeurs(): Collection
    {
        return $this->gallerieVendeurs;
    }

    public function addGallerieVendeur(GallerieVendeur $gallerieVendeur): self
    {
        if (!$this->gallerieVendeurs->contains($gallerieVendeur)) {
            $this->gallerieVendeurs->add($gallerieVendeur);
            $gallerieVendeur->setVendeur($this);
        }

        return $this;
    }

    public function removeGallerieVendeur(GallerieVendeur $gallerieVendeur): self
    {
        if ($this->gallerieVendeurs->removeElement($gallerieVendeur)) {
            // set the owning side to null (unless already changed)
            if ($gallerieVendeur->getVendeur() === $this) {
                $gallerieVendeur->setVendeur(null);
            }
        }

        return $this;
    }

    public function getTypeEtablissement(): ?string
    {
        return $this->type_etablissement;
    }

    public function setTypeEtablissement(string $type_etablissement): self
    {
        $this->type_etablissement = $type_etablissement;

        return $this;
    }
}
