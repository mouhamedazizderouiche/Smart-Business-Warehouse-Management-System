<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/mon-panier', name: 'mon_panier')]
    public function voirPanier(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();
        return $this->render('commande/mon_panier.html.twig', [
            'commandes' => $commandes
        ]);
    }
    #[Route('/commande/finaliser', name: 'finaliser_commande')]
    public function finaliserCommande(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();
        $total = array_reduce($commandes, function ($acc, $commande) {
            return $acc + ($commande->getProduit()->getPrixUnitaire() * $commande->getQuantite());
        }, 0);
    
        return $this->render('commande/finalisation.html.twig', [
            'commandes' => $commandes,
            'total' => $total
        ]);
    }
    
    #[Route('/produit/ajouter-au-panier/{id}/{quantite}', name: 'ajouter_commande')]
    public function ajouterAuPanier(Produit $produit, int $quantite, EntityManagerInterface $entityManager, CommandeRepository $commandeRepository): Response
    {
        $commandeExistante = $commandeRepository->findOneBy(['produit' => $produit]);

        if ($commandeExistante) {
            $commandeExistante->setQuantite($commandeExistante->getQuantite() + $quantite);
        } else {
            $commande = new Commande();
            $commande->setProduit($produit);
            $commande->setQuantite($quantite);
            $entityManager->persist($commande);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Produit ajouté au panier avec succès ✅');
        return $this->redirectToRoute('mon_panier');
    }

    #[Route('/commande/supprimer/{id}', name: 'supprimer_commande')]
    public function supprimerCommande(EntityManagerInterface $entityManager, Commande $commande): Response
    {
        $entityManager->remove($commande);
        $entityManager->flush();

        return $this->redirectToRoute('mon_panier');
    }

    #[Route('/commande/modifier/{id}', name: 'modifier_quantite', methods: ['POST'])]
    public function modifierQuantite(Request $request, EntityManagerInterface $entityManager, Commande $commande): Response
    {
        $nouvelleQuantite = $request->request->get('quantite');

        if (!is_numeric($nouvelleQuantite) || $nouvelleQuantite < 1) {
            $this->addFlash('error', 'Quantité invalide.');
            return $this->redirectToRoute('mon_panier');
        }

        $commande->setQuantite((int) $nouvelleQuantite);
        $entityManager->flush();
        $this->addFlash('success', 'Quantité mise à jour avec succès ✅');

        return $this->redirectToRoute('mon_panier');
    }
}
