<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

use Hateoas\Configuration\Annotation as Hateoas;
/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *      "evenement.getEvenement",
 *      parameters={
 *      "idEvenement" = "expr(object.getId())"
 *       }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAllEvenements")
 * )
 *
 */

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private ?string $image = null;

    #[ORM\ManyToMany(targetEntity: Invite::class, mappedBy: 'evenementID')]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private Collection $invites;

    #[ORM\Column(length: 255)]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private ?string $typeEvenement = null;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Localisation::class)]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private Collection $localisation;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'evenements')]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private Collection $participant;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favori')]
    #[Groups(['getEvenement', 'getAllEvenements'])]
    private Collection $users;

    public function __construct()
    {
        $this->invites = new ArrayCollection();
        $this->localisation = new ArrayCollection();
        $this->participant = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Invite>
     */
    public function getInvites(): Collection
    {
        return $this->invites;
    }

    public function addInvite(Invite $invite): self
    {
        if (!$this->invites->contains($invite)) {
            $this->invites->add($invite);
            $invite->addEvenementID($this);
        }

        return $this;
    }

    public function removeInvite(Invite $invite): self
    {
        if ($this->invites->removeElement($invite)) {
            $invite->removeEvenementID($this);
        }

        return $this;
    }

    public function getTypeEvenement(): ?string
    {
        return $this->typeEvenement;
    }

    public function setTypeEvenement(string $typeEvenement): self
    {
        $this->typeEvenement = $typeEvenement;

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
            $localisation->setEvenement($this);
        }

        return $this;
    }

    public function removeLocalisation(Localisation $localisation): self
    {
        if ($this->localisation->removeElement($localisation)) {
            // set the owning side to null (unless already changed)
            if ($localisation->getEvenement() === $this) {
                $localisation->setEvenement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipant(): Collection
    {
        return $this->participant;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participant->contains($participant)) {
            $this->participant->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participant->removeElement($participant);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addFavori($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeFavori($this);
        }

        return $this;
    }
}
