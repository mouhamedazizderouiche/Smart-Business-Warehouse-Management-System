<?php

namespace App\Controller\produit;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Form\CategorieType;
use App\Form\SubCategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'liste_categories')]
    public function listeCategories(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Categorie::class)->findBy(['parent' => null]);

        return $this->render('produit/categorie.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/categorie/ajout', name: 'ajout_categorie')]
    public function ajoutCategorie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie, [
            'attr' => ['novalidate' => 'novalidate']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imgUrl')->getData();

            if ($imageFile) {
                $extension = strtolower($imageFile->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($extension, $allowedExtensions)) {
                    $newFilename = uniqid() . '.' . $extension;

                    try {
                        $imageFile->move(
                            $this->getParameter('images_directory'),
                            $newFilename
                        );

                        $categorie->setImgUrl('uploads/images/' . $newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image.');
                    }
                } else {
                    $this->addFlash('error', 'Le fichier téléchargé n\'est pas une image valide. Extensions autorisées: .jpg, .jpeg, .png, .gif.');
                }
            }

            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a été ajoutée avec succès.');
            return $this->redirectToRoute('liste_categories');
        }

        return $this->render('produit/categorieAjout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categorie/{id}/ajout-sous-categorie', name: 'ajout_sous_categorie')]
    public function ajoutSousCategorie(Request $request, EntityManagerInterface $entityManager, Categorie $parentCategorie): Response
    {
        $subCategorie = new Categorie();
        $subCategorie->setParent($parentCategorie);

        $form = $this->createForm(SubCategorieType::class, $subCategorie, [
            'attr' => ['novalidate' => 'novalidate']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imgUrl')->getData();

            if ($imageFile) {
                $extension = strtolower($imageFile->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($extension, $allowedExtensions)) {
                    $newFilename = uniqid() . '.' . $extension;

                    try {
                        $imageFile->move(
                            $this->getParameter('images_directory'),
                            $newFilename
                        );

                        $subCategorie->setImgUrl('uploads/images/' . $newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image.');
                    }
                } else {
                    $this->addFlash('error', 'Le fichier téléchargé n\'est pas une image valide. Extensions autorisées: .jpg, .jpeg, .png, .gif.');
                }
            }

            $entityManager->persist($subCategorie);
            $entityManager->flush();

            $this->addFlash('success', 'La sous-catégorie a été ajoutée avec succès.');
            return $this->redirectToRoute('liste_categories');
        }

        return $this->render('produit/sousCategorieAjout.html.twig', [
            'form' => $form->createView(),
            'parentCategorie' => $parentCategorie,
        ]);
    }

    #[Route('/categorie/supprimer/{id}', name: 'supprimer_categorie')]
    public function supprimerCategorie(EntityManagerInterface $entityManager, Categorie $categorie): Response
    {
        $entityManager->remove($categorie);
        $entityManager->flush();

        return $this->redirectToRoute('liste_categories');
    }

    #[Route('/categorie/{slug}', name: 'categorie_show')]
    public function show(EntityManagerInterface $entityManager,Categorie $categorie): Response
    {
        $products = $entityManager->getRepository(Produit::class)->findAll();
        return $this->render('produit/categorieShow.html.twig', [
            'categorie' => $categorie,
            'produits' => $products,
        ]);
    }
}