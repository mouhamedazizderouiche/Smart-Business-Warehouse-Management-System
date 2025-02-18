<?php

namespace App\Controller;

use App\Entity\Region;
use App\Form\RegionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class RegionController extends AbstractController
{


    #[Route('/region/ajouter', name: 'app_region_ajouter')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $region = new Region();

        // Création du formulaire pour l'ajout de la région
        $form = $this->createForm(RegionType::class, $region);

        // Traitement de la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder la région dans la base de données
            $entityManager->persist($region);
            $entityManager->flush();

            // Message flash de succès
            $this->addFlash('success', 'Région ajoutée avec succès.');

            // Redirection vers la liste des régions
            return $this->redirectToRoute('app_region');
        }

        // Retourner le formulaire pour l'affichage
        return $this->render('region/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    // Afficher la liste des régions
    #[Route('/region', name: 'app_region')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les régions depuis la base de données
        $regions = $entityManager->getRepository(Region::class)->findAll();

        // Retourner la vue avec la liste des régions
        return $this->render('region/index.html.twig', [
            'regions' => $regions,
        ]);
    }            

    // Voir une région
    #[Route('/region/{id}', name: 'app_region_voir')]
    public function voir(Region $region): Response
    {
        // Afficher une région spécifique
        return $this->render('region/voir.html.twig', [
            'region' => $region,
        ]);
    }

    // Ajouter une région
    

    // Modifier une région
    #[Route('/region/modifier/{id}', name: 'app_region_modifier')]
    public function modifier(Request $request, Region $region, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire de modification
        $form = $this->createForm(RegionType::class, $region);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder les modifications dans la base de données
            $entityManager->flush();

            // Message flash de succès
            $this->addFlash('success', 'Région modifiée avec succès.');

            // Redirection vers la liste des régions
            return $this->redirectToRoute('app_region');
        }

        // Retourner le formulaire de modification
        return $this->render('region/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Supprimer une région
    #[Route('/region/supprimer/{id}', name: 'app_region_supprimer')]
    public function supprimer(Region $region, EntityManagerInterface $entityManager): Response
    {
        // Supprimer la région de la base de données
        $entityManager->remove($region);
        $entityManager->flush();

        // Message flash de succès
        $this->addFlash('success', 'Région supprimée avec succès.');

        // Redirection vers la liste des régions
        return $this->redirectToRoute('app_region');
    }
}
