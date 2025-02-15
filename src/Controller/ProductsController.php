<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ProductType;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProductsController extends AbstractController
{
    #[Route('/produit/ajout', name: 'ajout_produit')]
    public function home(Request $request, EntityManagerInterface $entityManager): Response
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

            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('liste_produits');
        }

        return $this->render('produit/productcreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produit', name: 'liste_produits')]
    public function listeProduits(EntityManagerInterface $entityManager): Response
    {
        
        $produits = $entityManager->getRepository(Produit::class)->findAll();

        return $this->render('produit/produit.html.twig', [
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


}
