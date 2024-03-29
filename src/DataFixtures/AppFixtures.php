<?php

namespace App\DataFixtures;

use App\Entity\Evenement;
use App\Entity\Invite;
use App\Entity\Localisation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;
    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher) {
        $this->faker = Factory::create("fr_FR");
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        //admin
        $admin = new User();
        $password = 'test';
        $admin->setUsername('admin')
            ->setEmail('admin@test.com')
            ->setPassword($this->userPasswordHasher->hashPassword($admin, $password))
            ->setRole('admin');
        $manager->persist($admin);

        for($i = 0; $i < 5;$i++) {

            $user = new User();
            $password = $this->faker->password(6, 12);
            $username = $this->faker->userName();
            $user->setUsername($username)
                ->setEmail($username . '@gmail.com')
                ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
                ->setRole('user');
            $manager->persist($user);

            $invite = new Invite();
            $invite->setName($this->faker->userName());
            $manager->persist($invite);

            $evenement = new Evenement();
            $evenement->setName($this->faker->company())
                ->setDescription($this->faker->text())
                ->setDateDebut($this->faker->dateTimeBetween('2022-01-01', '2025-12-31'))
                ->setDateFin($this->faker->dateTimeBetween($evenement->getDateDebut(), '2025-12-31'))
                ->setTypeEvenement('festival')
                ->addInvite($invite)
                ->addParticipant($user);
            $manager->persist($evenement);

            $user->addFavori($evenement);
            $manager->persist($user);

            $localisation = new Localisation();
            $localisation->setEvenement($evenement)
                ->setRue($this->faker->streetAddress())
                ->setCP($this->faker->postcode())
                ->setVille($this->faker->city());
            $manager->persist($localisation);
        }

        $manager->flush();
    }
}
