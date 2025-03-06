<?php

namespace App\Controller;

use App\Entity\CommentaireEvent;
use App\Form\CommentaireEventType;
use App\Entity\Evenement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireEventController extends AbstractController
{
    // Route to add a new comment
    #[Route('/evenement/{id}/commenter', name: 'app_commentaire_event_new', methods: ['GET', 'POST'])]
    public function ajouterCommentaire(
        Request $request, 
        Evenement $evenement, 
        EntityManagerInterface $entityManager
    ): Response {
        // Check if the user is logged in
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un commentaire.');
            return $this->redirectToRoute('app_login');
        }

        // Create and handle the form for the comment
        $commentaire = new CommentaireEvent(); 
        $form = $this->createForm(CommentaireEventType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Link the comment to the event and the logged-in user
            $commentaire->setEvenement($evenement);
            $commentaire->setUser($this->getUser());
            $commentaire->setDateCreation(new \DateTime()); // Set the creation date

            // Save the comment to the database
            $entityManager->persist($commentaire);
            $entityManager->flush();

            // Success message and redirect back to the event detail page
            $this->addFlash('success', 'Commentaire ajouté avec succès !');
            return $this->redirectToRoute('detail_evenement', ['id' => $evenement->getId()]);
        }

        return $this->render('evenement/detail.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
            'commentaires' => $evenement->getCommentaireEvents(), // Display associated comments
        ]);
    }

    // Route to show the event details
    #[Route('/evenement/{id}', name: 'detail_evenement', methods: ['GET'])]
    public function detail(Evenement $evenement): Response
    {
        return $this->render('evenement/detail.html.twig', [
            'evenement' => $evenement,
            'commentaires' => $evenement->getCommentaireEvents(), // Display comments associated with the event
        ]);
    }
}
