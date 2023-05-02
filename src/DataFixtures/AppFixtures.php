<?php

namespace App\DataFixtures;

use App\Entity\Evenement;
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
        //users
        for($i = 0; $i < 5;$i++) {
            $user = new User();
            $user->setUsername('user')
                ->setEmail('user@test.com')
                ->setPassword('password')
                ->setRole('user');
            $manager->persist($user);
        }

        for($i = 0; $i < 5;$i++) {
            $evenement = new Evenement();
            $evenement->setName('test')
                ->setDescription('description')
                ->setDateDebut(new \DateTime())
                ->setDateFin(new \DateTime())
                ->setTypeEvenement('festival');
            $manager->persist($evenement);

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
