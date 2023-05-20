<?php

namespace App\Entity;

use App\Repository\UtilisateursRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateursRepository::class)]
class Utilisateurs implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, unique: false)]
    private ?string $mobile = null;

    #[ORM\Column(length: 255)]
    private ?string $photo = null;

    #[ORM\Column]
    private array $role = [];

    #[ORM\Column]
    private ?string $date_creation = null;

    #[Groups(['vendeurs'])]
    #[ORM\OneToMany(mappedBy: 'Utilisateur', targetEntity: Vendeur::class)]
    private Collection $vendeurs;

    #[Groups(['rendezvous'])]
    #[ORM\OneToMany(mappedBy: 'Utilisateur', targetEntity: RendezVous::class)]
    private Collection $rendezVouses;

    #[Groups(['localisation'])]
    #[ORM\ManyToOne(inversedBy: 'Utilisateur')]
    private ?Geolocalisation $geolocalisation = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $login = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $Pays = null;

    #[ORM\Column(length: 255)]
    private ?string $Langue = null;

    #[ORM\Column(length: 255)]
    private ?string $codePostal = null;

    #[ORM\OneToMany(mappedBy: 'Utilisateur', targetEntity: VendeurNote::class)]
    private Collection $vendeurNotes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Token = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $compteActif = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $compteConfirme = null;

    public function __construct()
    {
        $this->vendeurs = new ArrayCollection();
        $this->rendezVouses = new ArrayCollection();
        $this->setDateCreation();
        $this->vendeurNotes = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getRole(): array
    {
        return $this->role;
    }

    public function setRole(array $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function setDateCreation(): self
    {
        $this->date_creation = date("Y-m-d H:i:s");

        return $this;
    }

    public function getDateCreation(): string
    {
        return $this->date_creation;
    }

    /**
     * @return Collection<int, Vendeur>
     */
    public function getVendeurs(): Collection
    {
        return $this->vendeurs;
    }

    public function addVendeur(Vendeur $vendeur): self
    {
        if (!$this->vendeurs->contains($vendeur)) {
            $this->vendeurs->add($vendeur);
            $vendeur->setUtilisateur($this);
        }

        return $this;
    }

    public function removeVendeur(Vendeur $vendeur): self
    {
        if ($this->vendeurs->removeElement($vendeur)) {
            // set the owning side to null (unless already changed)
            if ($vendeur->getUtilisateur() === $this) {
                $vendeur->setUtilisateur(null);
            }
        }

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
            $rendezVouse->setUtilisateur($this);
        }

        return $this;
    }

    public function removeRendezVouse(RendezVous $rendezVouse): self
    {
        if ($this->rendezVouses->removeElement($rendezVouse)) {
            // set the owning side to null (unless already changed)
            if ($rendezVouse->getUtilisateur() === $this) {
                $rendezVouse->setUtilisateur(null);
            }
        }

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

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

     /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPays(): ?string
    {
        return $this->Pays;
    }

    public function setPays(string $Pays): self
    {
        $this->Pays = $Pays;

        return $this;
    }

    public function getLangue(): ?string
    {
        return $this->Langue;
    }

    public function setLangue(string $Langue): self
    {
        $this->Langue = $Langue;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
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
            $vendeurNote->setUtilisateur($this);
        }

        return $this;
    }

    public function removeVendeurNote(VendeurNote $vendeurNote): self
    {
        if ($this->vendeurNotes->removeElement($vendeurNote)) {
            // set the owning side to null (unless already changed)
            if ($vendeurNote->getUtilisateur() === $this) {
                $vendeurNote->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->Token;
    }

    public function setToken(?string $Token): self
    {
        $this->Token = $Token;

        return $this;
    }

    public function getCompteActif(): ?string
    {
        return $this->compteActif;
    }

    public function setCompteActif(?string $compteActif): self
    {
        $this->compteActif = $compteActif;

        return $this;
    }

    public function getCompteConfirme(): ?string
    {
        return $this->compteConfirme;
    }

    public function setCompteConfirme(?string $compteConfirme): self
    {
        $this->compteConfirme = $compteConfirme;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

}
