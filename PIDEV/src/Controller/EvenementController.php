<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\EvenementRepository;

final class EvenementController extends AbstractController
{
    // Afficher la liste des événements
    #[Route('/evenement', name: 'app_evenement')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $evenements = $entityManager->getRepository(Evenement::class)->findAll();


        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }


    #[Route('/evenements', name: 'app_evenement_liste')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        // Récupérer la liste des événements depuis la base de données
        $evenements = $entityManager->getRepository(Evenement::class)->findAll();

        // Passer les événements au template
        return $this->render('evenement/liste.html.twig', [
            'evenements' => $evenements,
        ]);
    }

// Voir un événement
    #[Route('/evenement/{id}', name: 'app_evenement_voir')]
    public function voir(Evenement $evenement): Response
    {
        return $this->render('evenement/voir.html.twig', [
            'evenement' => $evenement,
        ]);
    }





    // Ajouter un événement avec stockage de la photo dans le répertoire
    #[Route('/evenement/ajout', name: 'app_evenement_ajout')]
    public function ajout(Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de la photo
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $photoFilename = uniqid() . '.' . $photoFile->guessExtension();
                $photoFile->move(
                    $params->get('photos_directory'),
                    $photoFilename
                );
                $evenement->setPhoto($photoFilename);
            }

            // Gestion des régions
            $regions = $form->get('regions')->getData();
            foreach ($regions as $region) {
                $evenement->addRegion($region); // Utilise la méthode addRegion de l'entité Evenement
            }

            $entityManager->persist($evenement);
            $entityManager->flush();

            $this->addFlash('success', 'Événement ajouté avec succès !');
            return $this->redirectToRoute('app_evenement');
        }

        return $this->render('evenement/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Modifier un événement avec gestion de la photo
    #[Route('/evenement/modifier/{id}', name: 'app_evenement_modifier')]
    public function modifier(Request $request, Evenement $evenement, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photoFile')->getData();

            if ($photoFile) {
                $photoFilename = uniqid() . '.' . $photoFile->guessExtension();
                $photoFile->move(
                    $params->get('photos_directory'),
                    $photoFilename
                );
                $evenement->setPhoto($photoFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Événement modifié avec succès.');

            return $this->redirectToRoute('app_evenement');
        }

        return $this->render('evenement/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Supprimer un événement et la photo associée
    #[Route('/evenement/supprimer/{id}', name: 'app_evenement_supprimer')]
    public function supprimer(Evenement $evenement, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        // Supprimer le fichier de la photo si elle existe
        $filesystem = new Filesystem();
        $photoPath = $params->get('photos_directory') . '/' . $evenement->getPhoto();
        if ($filesystem->exists($photoPath)) {
            $filesystem->remove($photoPath);
        }

        $entityManager->remove($evenement);
        $entityManager->flush();
        $this->addFlash('success', 'Événement supprimé avec succès.');

        return $this->redirectToRoute('app_evenement');
    }

//afficher client
    #[Route('/public/evenements', name: 'app_evenements_front')]
    public function afficherEvenementsFront(EvenementRepository $evenementRepository): Response
    {
        $evenements = $evenementRepository->findAll();

        return $this->render('evenement/evenements_front.html.twig', [
            'evenements' => $evenements,
        ]);
    }


}



