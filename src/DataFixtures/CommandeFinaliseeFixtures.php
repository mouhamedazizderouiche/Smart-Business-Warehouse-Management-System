<?php

namespace App\DataFixtures;

use App\Entity\CommandeFinalisee;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CommandeFinaliseeFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();

        // 🔹 Récupération des utilisateurs existants
        $utilisateurs = $manager->getRepository(User::class)->findAll();
        if (!$utilisateurs) {
            throw new \Exception("❌ Aucun utilisateur trouvé, assure-toi d'avoir exécuté les fixtures des utilisateurs !");
        }

        // 🔹 Récupération des produits existants
        $produits = $manager->getRepository(Produit::class)->findAll();
        if (!$produits) {
            throw new \Exception("❌ Aucun produit trouvé, assure-toi d'avoir exécuté les fixtures des produits !");
        }

        // 🔹 Création de 15 commandes finalisées
        for ($i = 0; $i < 15; $i++) {
            $commande = new CommandeFinalisee();
            
            $produit = $faker->randomElement($produits); // Sélectionne un produit aléatoire
            $utilisateur = $faker->randomElement($utilisateurs); // Sélectionne un utilisateur aléatoire
            
            $quantite = $faker->numberBetween(1, 5);
            
            $commande->setNomProduit($produit->getNom());
            $commande->setProduitId($produit->getId()); // Définir l'ID du produit
            $commande->setQuantite($quantite);
            $commande->setPrixTotal($produit->getPrixUnitaire() * $quantite);
            $commande->setdateCommande($faker->dateTimeBetween('-1 year', 'now'));
            $commande->setUser($utilisateur); // Assigner un utilisateur à la commande
            
            $manager->persist($commande);
        }

        $manager->flush();
    }

    /**
     * 🔹 Spécifier que cette fixture doit être exécutée **après** ProduitFixtures et UserFixtures.
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class , // Ajoute cette dépendance si nécessaire
            ProduitFixtures::class
        ];
    }
}
