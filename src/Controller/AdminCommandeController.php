<?php

namespace App\Controller;

use App\Entity\CommandeFinalisee;
use App\Entity\Produit;
use App\Repository\CommandeFinaliseeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminCommandeController extends AbstractController
{
    #[Route('/commandes', name: 'commandes')]
    public function index(
        CommandeFinaliseeRepository $commandeFinaliseeRepository,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $user = $this->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

        
        if (!$isAdmin) {
            return $this->redirectToRoute('shop_produits');
        }

       
        $query = $commandeFinaliseeRepository->createQueryBuilder('c')
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery();

        $commandes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            
        );

        
        $listeProduits = $entityManager->getRepository(Produit::class)->findAll();

        return $this->render('admin_commande/index.html.twig', [
            'commandes' => $commandes,
            'listeProduits' => $listeProduits,
        ]);
    }

    #[Route('/commandes/modifier/{id}', name: 'modifier_commande', methods: ['POST'])]
    public function modifierCommande(
        Request $request,
        CommandeFinalisee $commandeFinalisee,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

      
        if (!$isAdmin) {
            return new JsonResponse(['message' => '❌ Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['message' => '❌ Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($commandeFinalisee, $setter)) {
                $commandeFinalisee->$setter($value);
            }
        }

        $entityManager->flush();
        return new JsonResponse(['message' => '✅ Commande mise à jour avec succès !'], Response::HTTP_OK);
    }

    #[Route('/commandes/supprimer/{id}', name: 'supprimer_commande', methods: ['DELETE'])]
    public function supprimerCommande(
        CommandeFinalisee $commandeFinalisee,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

        
        if (!$isAdmin) {
            return new JsonResponse(['message' => '❌ Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($commandeFinalisee);
        $entityManager->flush();

        return new JsonResponse(['message' => '✅ Commande supprimée avec succès !'], Response::HTTP_OK);
    }
}
