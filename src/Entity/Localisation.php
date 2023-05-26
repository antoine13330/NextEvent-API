<?php

namespace App\Entity;

use App\Repository\LocalisationRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

use Hateoas\Configuration\Annotation as Hateoas;
/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *      "localisation.getLocalisation",
 *      parameters={
 *      "idLocalisation" = "expr(object.getId())"
 *       }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAllLocalisations")
 * )
 *
 */

#[ORM\Entity(repositoryClass: LocalisationRepository::class)]
class Localisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllEvenements'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getLocalisation', 'getAllLocalisations', 'getAllUsers'])]
    private ?string $rue = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getLocalisation', 'getAllLocalisations', 'getAllUsers'])]
    private ?string $CP = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getLocalisation', 'getAllLocalisations', 'getAllUsers'])]
    private ?string $ville = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getLocalisation', 'getAllLocalisations', 'getAllUsers'])]
    private ?string $coordonnees = null;

    #[ORM\ManyToOne(inversedBy: 'localisation')]
    #[Groups(['getLocalisation', 'getAllLocalisations'])]
    private ?Evenement $evenement = null;

    #[ORM\ManyToOne(inversedBy: 'localisation')]
    #[Groups(['getLocalisation', 'getAllLocalisations'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(?string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getCP(): ?string
    {
        return $this->CP;
    }

    public function setCP(?string $CP): self
    {
        $this->CP = $CP;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCoordonnees(): ?string
    {
        return $this->coordonnees;
    }

    public function setCoordonnees(?string $coordonnees): self
    {
        $this->coordonnees = $coordonnees;

        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
