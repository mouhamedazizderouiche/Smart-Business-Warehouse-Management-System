<?php

namespace App\Controller;
use App\Entity\CommandeFinalisee;
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
    #[Route('/paiement', name: 'paiement')]
    public function paiement(Request $request): Response
    {
        return $this->render('commande/paiement.html.twig');
    }
    #[Route('/traiter-paiement', name: 'traiter_paiement', methods: ['POST'])]
    public function traiterPaiement(Request $request): Response
    {
        $nom = $request->request->get('nom');
        $numero = $request->request->get('numero');
        $expiration = $request->request->get('expiration');
        $cvv = $request->request->get('cvv');
    
        
        if (!$nom || !$numero || !$expiration || !$cvv) {
            $this->addFlash('error', 'Veuillez remplir tous les champs.');
            return $this->redirectToRoute('paiement');
        }
    
       
        $this->addFlash('success', 'Paiement effectué avec succès ! ✅');
    
        
        return $this->redirectToRoute('historique_commandes');
    }
        
    #[Route('/commande/historique', name: 'historique_commandes')]
public function historiqueCommandes(EntityManagerInterface $entityManager): Response
{
    $historique = $entityManager->getRepository(CommandeFinalisee::class)->findBy([], ['dateCommande' => 'DESC']);

    return $this->render('commande/historique_commandes.html.twig', [
        'commandesFinalisees' => $historique
    ]);
}

    #[Route('/commande/finaliser', name: 'finaliser_commande')]
    public function finaliserCommande(EntityManagerInterface $entityManager, CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();
    
        if (empty($commandes)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('mon_panier');
        }
    
        foreach ($commandes as $commande) {
            $commandeFinalisee = new CommandeFinalisee();
            $commandeFinalisee->setNomProduit($commande->getProduit()->getNom());
            $commandeFinalisee->setQuantite($commande->getQuantite());
            $commandeFinalisee->setPrixTotal($commande->getProduit()->getPrixUnitaire() * $commande->getQuantite());
    
            $entityManager->persist($commandeFinalisee);
            $entityManager->remove($commande);
        }
    
        $entityManager->flush();
    
        $this->addFlash('success', 'Votre commande a été finalisée avec succès ! ✅');
        return $this->redirectToRoute('confirmation_commande');
    }
    
#[Route('/commande/confirmation', name: 'confirmation_commande')]
public function confirmationCommande(): Response
{
    return $this->render('commande/confirmation_commande.html.twig');
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
