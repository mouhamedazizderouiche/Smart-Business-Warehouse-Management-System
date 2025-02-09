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
use App\Entity\MessageReclamation;
use App\Form\MessageReclamationType;
#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/view/{id?}', name: 'reclamation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, ?string $id = null): Response
    {
        // Get all reclamations
        $reclamations = $em->getRepository(Reclamations::class)->findAll();

        // Build an array mapping reclamation IDs (as strings) to their messages
        $reclamationMessages = [];
        foreach ($reclamations as $reclamation) {
            $messages = $em->getRepository(MessageReclamation::class)
                ->findBy(['reclamation' => $reclamation]);
            $reclamationMessages[(string) $reclamation->getId()] = $messages;
        }

        $selectedReclamation = $id 
            ? $em->getRepository(Reclamations::class)->find($id) 
            : (!empty($reclamations) ? $reclamations[0] : null);

        return $this->render('reclamation/index.html.twig', [
            'reclamations'         => $reclamations,
            'reclamationMessages'  => $reclamationMessages,
            'selectedReclamation'  => $selectedReclamation,
        ]);
    }



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
    public function show(EntityManagerInterface $em): Response
    {
       
        $reclamations = $em->getRepository(Reclamations::class)->findAll();

        return $this->render('reclamation/show.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/{id}/edit', name: 'reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationsType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('reclamation_index');
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reclamation_index');
    }

    #[Route('/add/{reclamationId}', name: 'reclamation_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, string $reclamationId): Response
    {
        $reclamation = $entityManager->getRepository(Reclamations::class)->find($reclamationId);
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found.');
        }

        $form = $this->createForm(MessageReclamationType::class); 
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();
            $message->setDateMessage(new \DateTime());
            $message->setReclamation($reclamation);
            $entityManager->persist($message);
            $entityManager->flush();
        }

        return $this->render('reclamation/index.html.twig', [
            'selectedReclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }


     

    
}
