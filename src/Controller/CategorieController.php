<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'liste_categories')]
    public function listeCategories(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Categorie::class)->findAll();

        return $this->render('produit/categorie.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/categorie/ajout', name: 'ajout_categorie')]
    public function ajoutCategorie(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new Categorie object (the constructor will initialize the UUID)
        $categorie = new Categorie();

        // Create the form
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the category to the database
            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a été ajoutée avec succès.');
            return $this->redirectToRoute('liste_categories');
        }

        return $this->render('produit/categorieAjout.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}