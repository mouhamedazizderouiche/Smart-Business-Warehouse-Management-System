<?php

namespace App\DataFixtures;
use App\Entity\User;

use App\Entity\Produit;
use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\Uid\Uuid;

class ProduitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();
    
        // 🔹 Récupération des utilisateurs existants
        $utilisateurs = $manager->getRepository(User::class)->findAll();
        
        if (!$utilisateurs) {
            throw new \Exception("❌ Aucun utilisateur trouvé, assure-toi d'avoir exécuté les fixtures des utilisateurs !");
        }
    
        // 🔹 Création de 5 catégories pour les produits
        $categories = [];
        for ($i = 0; $i < 5; $i++) {
            $categorie = new Categorie();
            $categorie->setNom($faker->word);
            $manager->persist($categorie);
            $categories[] = $categorie;
        }
    
        $manager->flush(); // On sauvegarde les catégories en premier
    
        // 🔹 Création de 20 produits aléatoires
        for ($i = 0; $i < 20; $i++) {
            $produit = new Produit();
            $produit->setNom($faker->word);
            $produit->setDescription($faker->sentence(10));
            $produit->setPrixUnitaire($faker->randomFloat(2, 1, 100));
            $produit->setUrlImageProduit($faker->imageUrl(200, 200, 'products'));
            $produit->setQuantite($faker->numberBetween(5, 100));
            $produit->setCategorie($faker->randomElement($categories));
    
            // 🛠 Assigner un utilisateur aléatoire au produit
            $produit->setUser($faker->randomElement($utilisateurs));
    
            $manager->persist($produit);
        }
    
        $manager->flush();
    }
    
}
