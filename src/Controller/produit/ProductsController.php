<?php
namespace App\Controller\produit;

use App\Entity\produit\Categorie;
use App\Entity\produit\Produit;
use App\Form\ProductType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    #[Route('/produit/ajout', name: 'ajout_produit')]
    public function home(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProductType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('urlImageProduit')->getData();

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

                        $produit->setUrlImageProduit('uploads/images/' . $newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image.');
                    }

                } else {
                    $this->addFlash('error', 'Le fichier téléchargé n\'est pas une image valide. Extensions autorisées: .jpg, .jpeg, .png, .gif.');
                }
            }

            $user = $this->getUser();
            $produit->setUser($user);

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès!');
            return $this->redirectToRoute('liste_produits');
        }

        return $this->render('produit/productcreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/produit', name: 'liste_produits')]
    public function listeProduits(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $produits = $entityManager->getRepository(Produit::class)->findBy(['user' => $user]);

        return $this->render('produit/listeproduitdashboard.html.twig', [
            'produits' => $produits
        ]);
    }

    #[Route('/produit/edit/{id}', name: 'edit_produit')]
    public function editProduit(Request $request, EntityManagerInterface $entityManager, Produit $produit): Response
    {
        $form = $this->createForm(ProductType::class, $produit, [
            'is_edit' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('liste_produits');
        }

        return $this->render('produit/productcreate.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit
        ]);
    }

    #[Route('/produit/delete/{id}', name: 'delete_produit')]
    public function deleteProduit(EntityManagerInterface $entityManager, Produit $produit): Response
    {
        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->redirectToRoute('liste_produits');
    }


    #[Route('/produit/shop', name: 'shop_produits')]
    public function shopProduits(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager->getRepository(Produit::class)->findAll();
        $categories = $entityManager->getRepository(Categorie::class)->findAll();

        return $this->render('homepage/shop.html.twig', [
            'produits' => $produits,
            'categories' => $categories
        ]);
    }





}
