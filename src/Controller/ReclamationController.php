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
use ConsoleTVs\Profanity\Builder as Profanity;
use StatutReclamation;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Tag;
use Gemini;
use Knp\Component\Pager\PaginatorInterface;

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

        if ($form->isSubmitted()) {
            $title = $form->get('title')->getData();
            $description = $form->get('description')->getData();
            $hasProfanity = false;
            if (!Profanity::blocker($title)->clean()) {
                $this->addFlash('title', 'Do not use bad words in the title.');
                $hasProfanity = true;
            }
            if (!Profanity::blocker($description)->clean()) {
                $this->addFlash('des', 'Do not use bad words in the description.');
                $hasProfanity = true;
            }
            if ($hasProfanity) {
                return $this->redirectToRoute('reclamation_new');
            }
            if ($form->isValid()) {
                $reclamation->setDateReclamation(new \DateTime());
                $entityManager->persist($reclamation);
                $entityManager->flush();
                return $this->redirectToRoute('reclamation_show');
            }
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

        if ($form->isSubmitted() ) {

            $title = $form->get('title')->getData();
            $description = $form->get('description')->getData();
            $hasProfanity = false;
            if (!Profanity::blocker($title, languages: ['en', 'fr'])->clean()) {
                $this->addFlash('title', 'Do not use bad words in the title.');
                $hasProfanity = true;
            }
            if (!Profanity::blocker($description, languages: ['en', 'fr'])->clean()) {
                $this->addFlash('des', 'Do not use bad words in the description.');
                $hasProfanity = true;
            }
            if ($hasProfanity) {
                return $this->redirectToRoute('reclamation_newReview');
            }

            if ($form->isValid()) {
            $reclamation->setDateReclamation(new \DateTime());
            $reclamation->setStatut(StatutReclamation::AVIS);
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('testimonial');
        }
    }
        return $this->render('reclamation/newReview.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }


    // #[Route('/', name: 'reclamation_show', methods: ['GET'])]
    // public function show(Request $request, EntityManagerInterface $em): Response
    // {
    //     $user = $this->getUser();
    //     $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());
    
    //     $repository = $em->getRepository(Reclamations::class);
    //     $avis = $repository->findBy(['statut' => StatutReclamation::AVIS]);
    
    //     $limit = 4;
    //     $page = max(1, (int) $request->query->get('page', 1));
    //     $offset = ($page - 1) * $limit;
    //     $criteria = ['statut' => [StatutReclamation::EN_COURS, StatutReclamation::RESOLUE, StatutReclamation::FERMEE]];
    //     $totalReclamations = $repository->count($criteria);
    //     $totalPages = (int) ceil($totalReclamations / $limit);
    
    //     if ($page > $totalPages && $totalPages > 0) {
    //         throw $this->createNotFoundException("Page not found");
    //     }
    
    //     $reclamations = $repository->findBy($criteria, ['dateReclamation' => 'DESC'], $limit, $offset);
    

    //     foreach ($reclamations as $reclamation) {
    //         if ($reclamation->getTag() === null) {
    //             $this->assignTagToReclamation($em, $reclamation->getId());
    //         }
            
    //     }
    
    //     if ($isAdmin) {
    //         return $this->render('reclamation/dashboardrec.html.twig', [
    //             'reclamationsAvis' => $avis,
    //             'reclamations' => $reclamations,
    //             'currentPage'  => $page,
    //             'totalPages'   => $totalPages
    //         ]);
    //     } else {
    //         return $this->render('reclamation/show.html.twig', [
    //             'reclamationsAvis' => $avis,
    //             'reclamations' => $reclamations,
    //             'currentPage'  => $page,
    //             'totalPages'   => $totalPages
    //         ]);
    //     }
    // }

    #[Route('/', name: 'reclamation_show', methods: ['GET'])]
public function show(
    Request $request,
    EntityManagerInterface $em,
    PaginatorInterface $paginator
): Response {
    $user = $this->getUser();
    $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

    $repository = $em->getRepository(Reclamations::class);
    $avis = $repository->findBy(['statut' => StatutReclamation::AVIS]);

    // Get search query from request
    $searchTerm = $request->query->get('q');

    // Build query with filters
    $queryBuilder = $repository->createQueryBuilder('r')
        ->where('r.statut IN (:statuts)')
        ->setParameter('statuts', [
            StatutReclamation::EN_COURS,
            StatutReclamation::RESOLUE,
            StatutReclamation::FERMEE
        ]);

    // Add search filter if term exists
    if ($searchTerm) {
        $queryBuilder
            ->andWhere('r.title LIKE :searchTerm OR r.description LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');
    }

    $queryBuilder->orderBy('r.dateReclamation', 'DESC');

    // Paginate results
    $pagination = $paginator->paginate(
        $queryBuilder->getQuery(),
        $request->query->getInt('page', 1),
        4,
        ['distinct' => false]
    );

    foreach ($pagination as $reclamation) {
        if ($reclamation->getTag() === null) {
            $this->assignTagToReclamation($em, $reclamation->getId());
        }
    }

    $template = $isAdmin ? 'reclamation/dashboardrec.html.twig' : 'reclamation/show.html.twig';
    
    return $this->render($template, [
        'reclamationsAvis' => $avis,
        'reclamations' => $pagination,
        'searchTerm' => $searchTerm
    ]);
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


private function assignTagToReclamation(EntityManagerInterface $entityManager, Uuid $id): ?string
{
    $reclamation = $entityManager->getRepository(Reclamations::class)->find($id);
    if (!$reclamation) {
        return null;
    }

    $tags = $entityManager->getRepository(Tag::class)->findAll();
    $tagNames = [];
    
    foreach ($tags as $tag) {
        $tagNames[] = $tag->getName();
    }
    $formattedTags = implode(', ', $tagNames);

    $description = $reclamation->getDescription();
    $prompt = "answer with only one of those tags : " . $formattedTags . " to this reclamation " . $description;

    // $apiKey = "AIzaSyBaRoGkT-edsd9WToHHsSjEaCfaNzLcYM4"; 
    $apiKey = $_ENV['GEMINI_API_KEY'];
    if (!$apiKey) {
        throw new \RuntimeException('Gemini API key is not set in the environment.');
    }

    $client = Gemini::client($apiKey);
    $result = $client->geminiPro()->generateContent($prompt);
    $responseText = trim($result->text());

    $tag = $entityManager->getRepository(Tag::class)->findOneBy(['name' => $responseText]);

    if (!$tag) {
        return null;
    }
    $reclamation->setTag($tag);
    $entityManager->persist($reclamation);
    $entityManager->flush();

    return $responseText;
}


#[Route("/tag/{tagName}", name:"reclamation_filter_by_tag")]

    public function filterByTag(
        string $tagName,
        ReclamationsRepository $reclamationRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $queryBuilder = $reclamationRepository->createQueryBuilder('r')
            ->leftJoin('r.tag', 't')
            ->where('t.name = :name')
            ->setParameter('name', $tagName)
        ;
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            1
        );

        return $this->render('reclamation/filtredtags.html.twig', [
            'pagination' => $pagination,
            'name' => $tagName,
        ]);
    }

}
