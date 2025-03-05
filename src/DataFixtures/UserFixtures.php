<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();

        // 🔹 Création de 10 utilisateurs aléatoires
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setRoles(['ROLE_USER']);
            $user->setTravail($faker->jobTitle); 
            $user->setNom($faker->lastName);
            $user->setPrenom($faker->firstName);
            $user->setNumTel($faker->randomNumber(8));
            $user->setDateIscri($faker->dateTimeBetween('-2 years', 'now'));
            $user->setPhotoUrl($faker->imageUrl(100, 100, 'people'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
