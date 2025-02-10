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

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{

    #[Route('/new', name: 'reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamations();
        
        $form = $this->createForm(ReclamationsType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('reclamation_show');
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'reclamation_show', methods: ['GET'])]
public function show(Request $request, EntityManagerInterface $em): Response
{
    $repository = $em->getRepository(Reclamations::class);

    $limit = 5; 
    $page = max(1, (int) $request->query->get('page', 1)); 
    $offset = ($page - 1) * $limit; 

    $totalReclamations = $repository->count([]); 
    $reclamations = $repository->findBy([], ['dateReclamation' => 'DESC'], $limit, $offset);

    $totalPages = ceil($totalReclamations / $limit); 

    return $this->render('reclamation/show.html.twig', [
        'reclamations' => $reclamations,
        'currentPage' => $page,
        'totalPages' => $totalPages
    ]);
}


    #[Route('/{id}/edit', name: 'reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationsType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('reclamation_show');
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        
        $entityManager->remove($reclamation);
        $entityManager->flush();
        

        return $this->redirectToRoute('reclamation_show');
    }



     

    
}
