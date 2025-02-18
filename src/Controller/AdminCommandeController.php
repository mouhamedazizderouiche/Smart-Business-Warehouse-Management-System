<?php

namespace App\Controller;

use App\Entity\CommandeFinalisee;
use App\Repository\CommandeFinaliseeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminCommandeController extends AbstractController
{
    // ✅ Page principale de la gestion des commandes
    #[Route('/commandes', name: 'commandes')]
    public function index(CommandeFinaliseeRepository $commandeFinaliseeRepository): Response
    {
        // Récupérer toutes les commandes finalisées
        $commandes = $commandeFinaliseeRepository->findBy([], ['dateCommande' => 'DESC']);

        return $this->render('admin_commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    // 🔄 ✅ Modifier une commande en AJAX
    #[Route('/commandes/modifier/{id}', name: 'modifier_commande', methods: ['POST'])]
    public function modifierCommande(
        Request $request,
        CommandeFinalisee $commandeFinalisee,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['message' => '❌ Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Modifier les champs dynamiquement
        foreach ($data as $key => $value) {
            if (property_exists($commandeFinalisee, $key)) {
                $setter = 'set' . ucfirst($key);
                if (method_exists($commandeFinalisee, $setter)) {
                    $commandeFinalisee->$setter($value);
                }
            }
        }

        // Sauvegarde en base
        $entityManager->flush();

        return new JsonResponse(['message' => '✅ Commande mise à jour avec succès !'], Response::HTTP_OK);
    }

    // ❌ Supprimer une commande finalisée en AJAX
    #[Route('/commandes/supprimer/{id}', name: 'supprimer_commande', methods: ['DELETE'])]
    public function supprimerCommande(
        CommandeFinalisee $commandeFinalisee,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $entityManager->remove($commandeFinalisee);
        $entityManager->flush();

        return new JsonResponse(['message' => '✅ Commande supprimée avec succès !'], Response::HTTP_OK);
    }
}
