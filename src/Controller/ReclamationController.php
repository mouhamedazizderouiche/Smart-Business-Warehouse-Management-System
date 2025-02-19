<?php

namespace App\Controller;

use App\Entity\Reclamations;
use App\Form\ReclamationsType;
use App\Repository\ReclamationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use StatutReclamation;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{

    #[Route('/new', name: 'reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamations();
        $user = $this->getUser();
        if ($user) {
            $reclamation->setUser($user);
        }
        $form = $this->createForm(ReclamationsType::class, $reclamation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setDateReclamation(new \DateTime());
            $entityManager->persist($reclamation);
            $entityManager->flush();
            return $this->redirectToRoute('reclamation_show');
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/newReview', name: 'reclamation_newReview', methods: ['GET', 'POST'])]
    public function newReview(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamations();
        $user = $this->getUser();
        if ($user) {
            $reclamation->setUser($user);
        }
        $form = $this->createForm(ReclamationsType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setDateReclamation(new \DateTime());
            $reclamation->setStatut(StatutReclamation::AVIS);
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('testimonial');
        }
        return $this->render('reclamation/newReview.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/', name: 'reclamation_show', methods: ['GET'])]
    public function show(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());
        
        $repository = $em->getRepository(Reclamations::class);
        $avis = $em->getRepository(Reclamations::class)->findBy([
            'statut' => StatutReclamation::AVIS
        ]);
        $limit = 4;
        $page = max(1, (int) $request->query->get('page', 1));
        $offset = ($page - 1) * $limit;
        $criteria = ['statut' => [StatutReclamation::EN_COURS, StatutReclamation::RESOLUE, StatutReclamation::FERMEE]];
        $totalReclamations = $repository->count($criteria);
        $totalPages = (int) ceil($totalReclamations / $limit);
    
        if ($page > $totalPages && $totalPages > 0) {
            throw $this->createNotFoundException("Page not found");
        }
    
        $reclamations = $repository->findBy(
            $criteria,
            ['dateReclamation' => 'DESC'],
            $limit,
            $offset
        );
        if ($isAdmin) {
            return $this->render('reclamation/dashboardrec.html.twig', [
                'reclamationsAvis' => $avis,
                'reclamations' => $reclamations,
                'currentPage'  => $page,
                'totalPages'   => $totalPages
            ]);
        } else {
            return $this->render('reclamation/show.html.twig', [
                'reclamationsAvis' => $avis,
                'reclamations' => $reclamations,
                'currentPage'  => $page,
                'totalPages'   => $totalPages
            ]);
        }
        
    }
    
    


    #[Route('/{id}/edit', name: 'reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

        $form = $this->createForm(ReclamationsType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            return $this->redirectToRoute('reclamation_show');
            
        }
        if ($isAdmin) {
            return $this->render('reclamation/editrec.html.twig', [
                'reclamation' => $reclamation,
                'form' => $form->createView(),
            ]);
        } else {
            return $this->render('reclamation/edit.html.twig', [
                'reclamation' => $reclamation,
                'form' => $form->createView(),
            ]);
        }
    }
    
    #[Route('/{id}', name: 'reclamation_delete', methods: ['POST'])]
    public function delete(Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        
        $entityManager->remove($reclamation);
        $entityManager->flush();
        return $this->redirectToRoute('reclamation_show');
    }

    #[Route('/testimonial', name: 'testimonial')]
    public function testimonial(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());
        $reclamations = $entityManager->getRepository(Reclamations::class)->findBy([
            'statut' => StatutReclamation::AVIS
        ]);
    
        if ($isAdmin) {
            return $this->render('reclamation/dashboardrec.html.twig', [
                'reclamationsAvis' => $reclamations
            ]);
        } else {
            return $this->render('homepage/testimonial.html.twig', [
                'reclamationsAvis' => $reclamations
            ]);
           
        }
    }
    #[Route('/{id}/update-status', name: 'reclamation_update_status', methods: ['POST'])]
public function updateStatus(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
{
    $newStatus = $request->request->get('status');

    // Allowed status values
    $allowedStatuses = [
        StatutReclamation::EN_COURS->value,
        StatutReclamation::RESOLUE->value,
        StatutReclamation::FERMEE->value,
        StatutReclamation::AVIS->value,
    ];

    if (!in_array($newStatus, $allowedStatuses, true)) {
        return new Response(
            json_encode(['error' => 'Invalid status']),
            400,
            ['Content-Type' => 'application/json']
        );
    }
    $reclamation->setStatut(StatutReclamation::from($newStatus));
    $entityManager->flush();

    return new Response(
        json_encode(['success' => true, 'newStatus' => $newStatus]),
        200,
        ['Content-Type' => 'application/json']
    );
}

}
