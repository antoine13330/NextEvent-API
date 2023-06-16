<?php

namespace App\Entity;

use App\Repository\InviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

use Hateoas\Configuration\Annotation as Hateoas;
/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *      "invite.getAll",
 *      parameters={
 *      "idInvite" = "expr(object.getId())"
 *       }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAllInvites")
 * )
 *
 */

#[ORM\Entity(repositoryClass: InviteRepository::class)]
class Invite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllEvenements'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getInvite', 'getAllInvites'])]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'invites')]
    #[Groups(['getInvite', 'getAllInvites'])]
    private Collection $evenementID;

    public function __construct()
    {
        $this->evenementID = new ArrayCollection();
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

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenementID(): Collection
    {
        return $this->evenementID;
    }

    public function addEvenementID(Evenement $evenementID): self
    {
        if (!$this->evenementID->contains($evenementID)) {
            $this->evenementID->add($evenementID);
        }

        return $this;
    }

    public function removeEvenementID(Evenement $evenementID): self
    {
        $this->evenementID->removeElement($evenementID);

        return $this;
    }
}
