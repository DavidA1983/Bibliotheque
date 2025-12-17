<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: "integer")]
    private ?int $numero = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $annee = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $couverture = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $couvertureUpdatedAt = null;

    #[ORM\ManyToOne(targetEntity: Serie::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Serie $serie = null;

    #[ORM\Column(type: "boolean")]
    private bool $lu = false; // false par défaut

    // ---- Getters et Setters ----

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;
        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(?int $annee): self
    {
        $this->annee = $annee;
        return $this;
    }

    public function getCouverture(): ?string
    {
        return $this->couverture;
    }

    public function setCouverture(?string $couverture): self
    {
        $this->couverture = $couverture;
        return $this;
    }

    public function getCouvertureUpdatedAt(): ?\DateTimeInterface
    {
        return $this->couvertureUpdatedAt;
    }

    public function setCouvertureUpdatedAt(?\DateTimeInterface $couvertureUpdatedAt): self
    {
        $this->couvertureUpdatedAt = $couvertureUpdatedAt;
        return $this;
    }

    public function getSerie(): ?Serie
    {
        return $this->serie;
    }

    public function setSerie(?Serie $serie): self
    {
        $this->serie = $serie;
        return $this;
    }

    public function isLu(): bool
    {
        return $this->lu;
    }

    public function setLu(bool $lu): self
    {
        $this->lu = $lu;
        return $this;
    }
}

