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
    #[Route('/message/{id}/edit', name: 'message_reclamation_edit', methods: ['GET', 'POST'])]
    public function editMessage(Request $request, MessageReclamation $message, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MessageReclamationType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            // Redirect back to the messages view for the associated reclamation
            return $this->redirectToRoute('message_reclamation_index', ['id' => $message->getReclamation()->getId()]);
        }

        return $this->render('message_reclamation/edit.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }
     #[Route('/view/{id?}', name: 'message_reclamation_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, ?string $id = null): Response
    {

        $user = $this->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());
        $reclamations = $em->getRepository(Reclamations::class)->findAll();
        $reclamationMessages = [];
        foreach ($reclamations as $reclamation) {
            $messages = $em->getRepository(MessageReclamation::class)
                ->findBy(['reclamation' => $reclamation]);
            $reclamationMessages[(string)$reclamation->getId()] = $messages;
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
            $user = $this->getUser();
                if ($user) {
                    $messageReclamation->setUser($user);
                }
            $em->persist($messageReclamation);
            $em->flush();
            return $this->redirectToRoute('message_reclamation_index', ['id' => $selectedReclamation->getId()]);
        }

        if ($isAdmin) {
            return $this->render('message_reclamation/messagerec.html.twig', [
                'reclamations'         => $reclamations,
                'reclamationMessages'    => $reclamationMessages,
                'selectedReclamation'    => $selectedReclamation,
                'messageForm'            => $form->createView(),
            ]);
        }
        else {
            return $this->render('message_reclamation/index.html.twig', [
                'reclamations'         => $reclamations,
                'reclamationMessages'    => $reclamationMessages,
                'selectedReclamation'    => $selectedReclamation,
                'messageForm'            => $form->createView(),
            ]);
        }
       
    }

    


    #[Route('/message/{id}', name: 'message_reclamation_delete', methods: ['POST'])]
    public function deleteMessage(MessageReclamation $message, EntityManagerInterface $entityManager): Response
    {
        $reclamationId = $message->getReclamation()->getId();
        $entityManager->remove($message);
        $entityManager->flush();

        return $this->redirectToRoute('message_reclamation_index', ['id' => $reclamationId]);
    }

    



}
