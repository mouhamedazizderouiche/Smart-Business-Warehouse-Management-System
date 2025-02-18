<?php

namespace App\Controller;

use App\Repository\CommandeFinaliseeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminCommandeController extends AbstractController
{
    #[Route('/commandes', name: 'commandes')]
    public function index(CommandeFinaliseeRepository $commandeFinaliseeRepository): Response
    {
        // Récupérer toutes les commandes finalisées
        $commandes = $commandeFinaliseeRepository->findBy([], ['dateCommande' => 'DESC']);

        return $this->render('admin_commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}
