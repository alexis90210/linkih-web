<?php

namespace App\Entity;

use App\Repository\VendeurNoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VendeurNoteRepository::class)]
class VendeurNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vendeurNotes')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Vendeur $Vendeur = null;

    #[ORM\Column(length: 255)]
    private ?string $Note = null;

    #[ORM\ManyToOne(inversedBy: 'vendeurNotes')]
    private ?Utilisateurs $Utilisateur = null;

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

    public function getNote(): ?string
    {
        return $this->Note;
    }

    public function setNote(string $Note): self
    {
        $this->Note = $Note;

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
}
