<?php

namespace App\Controller;

use App\Entity\MessageReclamation;
use App\Form\MessageReclamationType;
use App\Repository\MessageReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Reclamations;


class MessageReclamationController extends AbstractController
{
     #[Route('/view/{id?}', name:'message_reclamation_index', methods:['GET', 'POST'])]

    public function index(Request $request, EntityManagerInterface $em, ?string $id = null): Response
    {
        $reclamations = $em->getRepository(Reclamations::class)->findAll();
        $reclamationMessages = [];
        foreach ($reclamations as $reclamation) {
            $messages = $em->getRepository(MessageReclamation::class)
                ->findBy(['reclamation' => $reclamation]);
            $reclamationMessages[(string) $reclamation->getId()] = $messages;
        }

        $selectedReclamation = $id 
            ? $em->getRepository(Reclamations::class)->find($id) 
            : (!empty($reclamations) ? $reclamations[0] : null);
        $messageReclamation = new MessageReclamation();
        if ($selectedReclamation) {
            $messageReclamation->setReclamation($selectedReclamation);
            $messageReclamation->setDateMessage(new \DateTime());
        }
        $form = $this->createForm(MessageReclamationType::class, $messageReclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($messageReclamation);
            $em->flush();
            return $this->redirectToRoute('message_reclamation_index', ['id' => $selectedReclamation ? $selectedReclamation->getId() : null]);
        }

        return $this->render('message_reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'reclamationMessages' => $reclamationMessages,
            'selectedReclamation' => $selectedReclamation,
            'form' => $form->createView(),
        ]);
    }


    // #[Route('/view/{id?}', name: 'message_reclamation_index', methods: ['GET'])]
    // public function index(EntityManagerInterface $em, ?string $id = null): Response
    // {
    //     // Get all reclamations
    //     $reclamations = $em->getRepository(Reclamations::class)->findAll();

    //     // Build an array mapping reclamation IDs (as strings) to their messages
    //     $reclamationMessages = [];
    //     foreach ($reclamations as $reclamation) {
    //         $messages = $em->getRepository(MessageReclamation::class)
    //             ->findBy(['reclamation' => $reclamation]);
    //         $reclamationMessages[(string) $reclamation->getId()] = $messages;
    //     }

    //     $selectedReclamation = $id 
    //         ? $em->getRepository(Reclamations::class)->find($id) 
    //         : (!empty($reclamations) ? $reclamations[0] : null);

    //     return $this->render('message_reclamation/index.html.twig', [
    //         'reclamations'         => $reclamations,
    //         'reclamationMessages'  => $reclamationMessages,
    //         'selectedReclamation'  => $selectedReclamation,
    //     ]);
    // }


    #[Route("/message-reclamation/{reclamationId}/new", name:"message_reclamation_new", methods:["GET", "POST"])]


    public function new(Request $request, EntityManagerInterface $em, string $reclamationId): Response
    {
        $reclamation = $em->getRepository(Reclamations::class)->find($reclamationId);
    
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found');
        }
    
        $messageReclamation = new MessageReclamation();
        $messageReclamation->setReclamation($reclamation);
        $messageReclamation->setDateMessage(new \DateTime());
    
        $form = $this->createForm(MessageReclamationType::class, $messageReclamation);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($messageReclamation);
            $em->flush();
    
            return $this->redirectToRoute('message_reclamation_index');
        }
    
        return $this->render('message_reclamation/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/message-reclamation/{id}/edit", name="message_reclamation_edit", requirements={"id"="\d+"})
     */
    public function edit(Request $request, MessageReclamation $messageReclamation, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MessageReclamationType::class, $messageReclamation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('message_reclamation_index');
        }

        return $this->render('message_reclamation/edit.html.twig', [
            'form' => $form->createView(), 
            'message_reclamation' => $messageReclamation,
        ]);
    }

    /**
     * @Route("/message-reclamation/{id}/delete", name="message_reclamation_delete", methods={"DELETE"})
     */
    public function delete(Request $request, MessageReclamation $messageReclamation, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$messageReclamation->getId(), $request->request->get('_token'))) {
            $em->remove($messageReclamation);
            $em->flush();
        }

        return $this->redirectToRoute('message_reclamation_index');
    }



}
