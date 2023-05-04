<?php

namespace App\DataFixtures;

use App\Entity\Evenement;
use App\Entity\Invite;
use App\Entity\Localisation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //admin
        $admin = new User();
        $admin->setUsername('admin')
            ->setEmail('admin@test.com')
            ->setPassword('password')
            ->setRole('admin');
        $manager->persist($admin);

        for($i = 0; $i < 5;$i++) {

            $user = new User();
            $user->setUsername('user')
                ->setEmail('user@test.com')
                ->setPassword('password')
                ->setRole('user');
            $manager->persist($user);

            $invite = new Invite();
            $invite->setName('test');
            $manager->persist($invite);

            $evenement = new Evenement();
            $evenement->setName('test')
                ->setDescription('description')
                ->setDateDebut(new \DateTime())
                ->setDateFin(new \DateTime())
                ->setTypeEvenement('festival')
                ->addInvite($invite)
                ->addParticipant($user);
            $manager->persist($evenement);

            $user->addFavori($evenement);
            $manager->persist($user);

            $localisation = new Localisation();
            $localisation->setEvenement($evenement)
                ->setRue('e')
                ->setCP('e')
                ->setVille('e');
            $manager->persist($localisation);
        }

        $manager->flush();
    }
}
