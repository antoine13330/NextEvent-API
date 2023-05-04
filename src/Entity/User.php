<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

use Hateoas\Configuration\Annotation as Hateoas;
/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *      "Evenements.getEvenement",
 *      parameters={
 *      "idEvenement" = "expr(object.getId())"
 *       }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAllEvenements")
 * )
 *
 */

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUser', 'getAllUser'])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUser', 'getAllUser'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUser', 'getAllUser'])]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getUser', 'getAllUser'])]
    private ?string $photo = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUser', 'getAllUser'])]
    private ?string $role = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Localisation::class)]
    #[Groups(['getUser', 'getAllUser'])]
    private Collection $localisation;

    #[ORM\ManyToMany(targetEntity: Evenement::class, mappedBy: 'participant')]
    #[Groups(['getUser', 'getAllUser'])]
    private Collection $evenements;

    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'users')]
    #[Groups(['getUser', 'getAllUser'])]
    private Collection $favori;

    public function __construct()
    {
        $this->localisation = new ArrayCollection();
        $this->evenements = new ArrayCollection();
        $this->favori = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection<int, Localisation>
     */
    public function getLocalisation(): Collection
    {
        return $this->localisation;
    }

    public function addLocalisation(Localisation $localisation): self
    {
        if (!$this->localisation->contains($localisation)) {
            $this->localisation->add($localisation);
            $localisation->setUser($this);
        }

        return $this;
    }

    public function removeLocalisation(Localisation $localisation): self
    {
        if ($this->localisation->removeElement($localisation)) {
            // set the owning side to null (unless already changed)
            if ($localisation->getUser() === $this) {
                $localisation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
            $evenement->addParticipant($this);
        }

        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        if ($this->evenements->removeElement($evenement)) {
            $evenement->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getFavori(): Collection
    {
        return $this->favori;
    }

    public function addFavori(Evenement $favori): self
    {
        if (!$this->favori->contains($favori)) {
            $this->favori->add($favori);
        }

        return $this;
    }

    public function removeFavori(Evenement $favori): self
    {
        $this->favori->removeElement($favori);

        return $this;
    }
}
