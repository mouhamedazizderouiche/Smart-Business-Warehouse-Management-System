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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\User;

use ConsoleTVs\Profanity\Builder as Profanity;
use StatutReclamation;
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

        
            
        if ($form->isSubmitted() ) {
            $user = $this->getUser();
                if ($user) {
                    $messageReclamation->setUser($user);
                }
            $contenu = $form->get('contenu')->getData();
            $hasProfanity = false;
            if (!Profanity::blocker($contenu, languages: ['en', 'fr'])->clean()) {
                $this->addFlash('contenu', 'Do not use bad words in the reply.');
                $hasProfanity = true;
            }
            if ($hasProfanity) {
                return $this->redirectToRoute('message_reclamation_index', ['id' => $selectedReclamation->getId()]);
            }
            if ($form->isValid()) {
                $em->persist($messageReclamation);
                $em->flush();
                return $this->redirectToRoute('message_reclamation_index', ['id' => $selectedReclamation->getId()]);
            }
            
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

    #[Route('/reclamation/{id}/auto-reply', name: 'reclamation_auto_reply', methods: ['POST'])]
    public function generateAutoReply(?string $id, EntityManagerInterface $em, HttpClientInterface $httpClient): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Fetch the reclamation by ID
        $reclamation = $em->getRepository(Reclamations::class)->find($id);
        if (!$reclamation) {
            return new JsonResponse(['error' => 'Reclamation not found'], Response::HTTP_NOT_FOUND);
        }

        // Construct the reclamation text (title + description)
        $reclamationText = trim($reclamation->getTitle() . ' ' . $reclamation->getDescription());
        if (empty($reclamationText)) {
            return new JsonResponse(['error' => 'Reclamation text is empty'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Make a POST request to the Flask API
            $response = $httpClient->request('POST', 'http://localhost:5000/predict', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'reclamation' => $reclamationText,
                ],
            ]);

            // Get the response data from Flask
            $data = $response->toArray();
            $autoResponse = $data['response'] ?? null;

            if (!$autoResponse) {
                return new JsonResponse(['error' => 'No response received from API'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

    
            $message = new MessageReclamation();
            $message->setReclamation($reclamation);
            $message->setUser($user); 
            $message->setContenu($autoResponse);  
            $message->setDateMessage(new \DateTime()); 

            $em->persist($message);
            $reclamation->setStatut(StatutReclamation::RESOLUE);
            $em->flush();

            // Return the response for the frontend
            return new JsonResponse([
                'success' => true,
                'message' => 'Auto-reply generated and saved successfully',
                'response' => $autoResponse,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to generate auto-reply: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/test-auto-reply', name: 'test_reclamation_auto_reply')]
    public function testAutoReply(EntityManagerInterface $em): Response
    {
        // Fetch all reclamations
        $reclamations = $em->getRepository(Reclamations::class)->findAll();
    
        return $this->render('reclamation/test_auto_reply.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
    
    



}
