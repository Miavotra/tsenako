<?php

namespace App\Entity;

use App\Repository\PrixVenteRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PrixVenteRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PrixVente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?int $valeur = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $createdAt = null;
 
    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?int $status = null;

    #[ORM\ManyToOne(inversedBy: 'prixVentes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeur(): ?int
    {
        return $this->valeur;
    }

    public function setValeur(int $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTime();
    }
 
    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }
}
