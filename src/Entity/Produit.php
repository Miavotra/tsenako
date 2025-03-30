<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_NAME_PRODUCT', fields: ['name'])]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['produit:read', 'produit:write'])]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime$createdAt = null;

    #[Groups(['produit:read', 'produit:write'])]
    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Category $category = null;

    /**
     * @var Collection<int, PrixVente>
     */
    #[ORM\OneToMany(targetEntity: PrixVente::class, mappedBy: 'produit')]
    #[Groups(['produit:read'])]
    private Collection $prixVentes;

    public function __construct()
    {
        $this->prixVentes = new ArrayCollection();
    }
  

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getPrixVente(): ?int
    {
        foreach($this->prixVentes as $prix) {
            if($prix->getStatus() == 1) return $prix->getValeur();
        }
        return 0;
    }

    /**
     * @return Collection<int, PrixVente>
     */
    public function getPrixVentes(): Collection
    {
        return $this->prixVentes;
    }

    public function addPrixVente(PrixVente $prixVente): static
    {
        if (!$this->prixVentes->contains($prixVente)) {
            $this->prixVentes->add($prixVente);
            $prixVente->setProduit($this);
        }

        return $this;
    }

    public function removePrixVente(PrixVente $prixVente): static
    {
        if ($this->prixVentes->removeElement($prixVente)) {
            // set the owning side to null (unless already changed)
            if ($prixVente->getProduit() === $this) {
                $prixVente->setProduit(null);
            }
        }

        return $this;
    }
 
}
