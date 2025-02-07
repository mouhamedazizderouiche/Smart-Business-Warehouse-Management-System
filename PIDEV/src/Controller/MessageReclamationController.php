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
    /**
     * @Route("/message-reclamation", name="message_reclamation_index")
     */
    public function index(MessageReclamationRepository $messageReclamationRepository): Response
    {
        
        $messageReclamations = $messageReclamationRepository->findAll();

        return $this->render('message_reclamation/index.html.twig', [
            'message_reclamations' => $messageReclamations,
        ]);
    }

    /**
     * @Route("/message-reclamation/{id}", name="message_reclamation_show", requirements={"id"="\d+"})
     */
    public function show(MessageReclamation $messageReclamation): Response
    {
        return $this->render('message_reclamation/show.html.twig', [
            'message_reclamation' => $messageReclamation,
        ]);
    }

    /**
     * @Route("/message-reclamation/{reclamationId}/new", name="message_reclamation_new", methods={"GET", "POST"})
     */

    public function new(Request $request, EntityManagerInterface $em, int $reclamationId): Response
    {
        $reclamation = $em->getRepository(Reclamations::class)->find($reclamationId);
    
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found');
        }
    
        $messageReclamation = new MessageReclamation();
        $messageReclamation->setReclamation($reclamation);
    
        $form = $this->createForm(MessageReclamationType::class, $messageReclamation);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($messageReclamation);
            $em->flush();
    
            return $this->redirectToRoute('message_reclamation_index');
        }
    
        return $this->render('message_reclamation/new.html.twig', [
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
