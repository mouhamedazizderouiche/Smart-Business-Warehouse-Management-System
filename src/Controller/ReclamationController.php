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
    
        $limit = 3;
        $page = max(1, (int) $request->query->get('page', 1));
        $offset = ($page - 1) * $limit;
        $criteria = ['statut' => StatutReclamation::EN_ATTENTE];
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
                'reclamations' => $reclamations,
                'currentPage'  => $page,
                'totalPages'   => $totalPages
            ]);
        } else {
            return $this->render('reclamation/show.html.twig', [
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
        $reclamations = $entityManager->getRepository(Reclamations::class)->findBy([
            'statut' => StatutReclamation::AVIS
        ]);
    
        return $this->render('homepage/testimonial.html.twig', [
            'reclamations' => $reclamations
        ]);
    }

    // #[Route('/recAdmin', name: 'recAdmin', methods: ['GET'])]
    // public function recAdmin(Request $request, EntityManagerInterface $em): Response
    // {
    //     $repository = $em->getRepository(Reclamations::class);
        
    //     $limit = 3; 
    //     $page = max(1, (int) $request->query->get('page', 1));
    //     $offset = ($page - 1) * $limit;
    //     $criteria = ['statut' => StatutReclamation::EN_ATTENTE];
    //     $totalReclamations = $repository->count($criteria);
    //     $totalPages = (int) ceil($totalReclamations / $limit);
    
    //     if ($page > $totalPages && $totalPages > 0) {
    //         throw $this->createNotFoundException("Page not found");
    //     }
    
    //     $reclamations = $repository->findBy(
    //         $criteria,
    //         ['dateReclamation' => 'DESC'],
    //         $limit,
    //         $offset
    //     );
    
    //     return $this->render('reclamation/dashboardrec.html.twig', [
    //         'reclamations' => $reclamations,
    //         'currentPage'  => $page,
    //         'totalPages'   => $totalPages
    //     ]);
    // }


    
    


    

     

    
}
