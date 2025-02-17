<?php

namespace App\Controller;

use App\Entity\Entrepot;
use App\Form\EntrepotType;
use App\Repository\EntrepotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/entrepot')]
class EntrepotController extends AbstractController
{
    // Affiche la liste de tous les entrepôts
    #[Route('/', name: 'app_entrepot_index', methods: ['GET'])]
    public function index(EntrepotRepository $entrepotRepository): Response
    {
        return $this->render('entrepot/index.html.twig', [
            'entrepots' => $entrepotRepository->findAll(), // Récupère tous les entrepôts
        ]);
    }

    // Affiche le formulaire de création d'un nouvel entrepôt
    #[Route('/new', name: 'app_entrepot_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entrepot = new Entrepot();
        $form = $this->createForm(EntrepotType::class, $entrepot);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Les données sont valides, on peut les enregistrer
            $entityManager->persist($entrepot);
            $entityManager->flush();
    
            $this->addFlash('success', 'L\'entrepôt a été créé avec succès.');
            return $this->redirectToRoute('app_entrepot_index');
        }
    
        // Si le formulaire n'est pas valide, on affiche les erreurs
        return $this->render('entrepot/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    // Affiche les détails d'un entrepôt spécifique
    #[Route('/{id}', name: 'app_entrepot_show', methods: ['GET'])]
    public function show(Entrepot $entrepot): Response
    {
        return $this->render('entrepot/show.html.twig', [
            'entrepot' => $entrepot, // Passe l'entrepôt à la vue
        ]);
    }

    // Affiche le formulaire de modification d'un entrepôt existant
    #[Route('/{id}/edit', name: 'app_entrepot_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entrepot $entrepot, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntrepotType::class, $entrepot); // Crée le formulaire de modification

        $form->handleRequest($request); // Gère la soumission du formulaire

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est valide, met à jour l'entrepôt en base de données
            $entityManager->flush();

            // Ajoute un message flash pour informer l'utilisateur
            $this->addFlash('success', 'L\'entrepôt a été modifié avec succès.');

            // Redirige vers la liste des entrepôts
            return $this->redirectToRoute('app_entrepot_index');
        }

        // Affiche le formulaire de modification
        return $this->render('entrepot/edit.html.twig', [
            'entrepot' => $entrepot,
            'form' => $form->createView(),
        ]);
    }

    // Supprime un entrepôt
    #[Route('/{id}', name: 'app_entrepot_delete', methods: ['POST'])]
    public function delete(Request $request, Entrepot $entrepot, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Vérifie la validité du token CSRF pour éviter les attaques
        $token = new CsrfToken('delete' . $entrepot->getId(), $request->request->get('_token'));
        if ($csrfTokenManager->isTokenValid($token)) {
            // Supprime l'entrepôt de la base de données
            $entityManager->remove($entrepot);
            $entityManager->flush();

            // Ajoute un message flash pour informer l'utilisateur
            $this->addFlash('success', 'L\'entrepôt a été supprimé avec succès.');
        } else {
            // Si le token CSRF est invalide, affiche une erreur
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        // Redirige vers la liste des entrepôts
        return $this->redirectToRoute('app_entrepot_index');
    }
}