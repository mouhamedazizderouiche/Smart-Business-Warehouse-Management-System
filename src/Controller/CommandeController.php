<?php

namespace App\Controller;
use App\Entity\Commande;
use App\Entity\CommandeFinalisee;
use App\Entity\Produit;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Snappy\Pdf;

class CommandeController extends AbstractController
{
    #[Route('/facture/{id}', name: 'facture_utilisateur')]
public function genererFactureUtilisateur(CommandeFinalisee $commandeFinalisee, Pdf $pdf, Security $security): Response
{
    $user = $security->getUser();

    
    if ($commandeFinalisee->getUser() !== $user) {
        throw $this->createAccessDeniedException("Vous n'avez pas accès à cette facture.");
    }

    $html = $this->renderView('facture/facture.html.twig', [
        'commande' => $commandeFinalisee
    ]);

    return new Response(
        $pdf->getOutputFromHtml($html),
        Response::HTTP_OK,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="facture.pdf"'
        ]
    );
}

    #[Route('/mon-panier', name: 'mon_panier')]
public function voirPanier(CommandeRepository $commandeRepository, Security $security): Response
{
    $user = $security->getUser();

    if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
        return $this->redirectToRoute('admin_commandes'); 
    }

    $commandes = $commandeRepository->findBy(['user' => $user]);

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
public function traiterPaiement(
    Request $request,
    EntityManagerInterface $entityManager,
    CommandeRepository $commandeRepository,
    Security $security
): Response {
    $nom = $request->request->get('nom');
    $numero = $request->request->get('numero');
    $expiration = $request->request->get('expiration');
    $cvv = $request->request->get('cvv');

    $user = $security->getUser();

    
    if (!$user) {
        $this->addFlash('error', '⚠️ Vous devez être connecté pour finaliser votre commande.');
        return $this->redirectToRoute('paiement');
    }

    
    if (!$nom || !$numero || !$expiration || !$cvv) {
        $this->addFlash('error', '⚠️ Tous les champs sont obligatoires.');
        return $this->redirectToRoute('paiement');
    }

    if (!is_numeric($numero) || strlen((string) $numero) !== 16) {
        $this->addFlash('error', '⚠️ Le numéro de carte doit contenir exactement 16 chiffres.');
        return $this->redirectToRoute('paiement');
    }

   
if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiration)) {
    $this->addFlash('error', '⚠️ La date d\'expiration doit être au format MM/YY.');
    return $this->redirectToRoute('paiement');
}


[$mois, $annee] = explode('/', $expiration);
$annee = (int) $annee;
$mois = (int) $mois;


$annee = ($annee < 50) ? 2000 + $annee : 1900 + $annee;

$dateExpiration = \DateTime::createFromFormat('Y-m', "$annee-$mois");
$dateActuelle = new \DateTime('first day of this month');


if ($dateExpiration < $dateActuelle) {
    $this->addFlash('error', '⚠️ La carte est expirée.');
    return $this->redirectToRoute('paiement');
}


    if (!is_numeric($cvv) || strlen((string) $cvv) !== 3) {
        $this->addFlash('error', '⚠️ Le CVV doit contenir exactement 3 chiffres.');
        return $this->redirectToRoute('paiement');
    }

    
    $commandes = $commandeRepository->findBy(['user' => $user]);

    if (empty($commandes)) {
        $this->addFlash('error', '⚠️ Votre panier est vide.');
        return $this->redirectToRoute('mon_panier');
    }

    foreach ($commandes as $commande) {
        $commandeFinalisee = new CommandeFinalisee();
        
        
        $commandeFinalisee->setProduitId($commande->getProduit()->getId());
      
      $commandeFinalisee->setNomProduit($commande->getProduit()->getNom());
        $commandeFinalisee->setProduitPrix($commande->getProduit()->getPrixUnitaire());
    
        
        $commandeFinalisee->setQuantite($commande->getQuantite());
        $commandeFinalisee->setPrixTotal($commande->getProduit()->getPrixUnitaire() * $commande->getQuantite());
        $commandeFinalisee->setUser($user);  
    
        $entityManager->persist($commandeFinalisee);
    
        
        $entityManager->remove($commande);
    }
    

    $entityManager->flush();

    
    $this->addFlash('success', '✅ Paiement effectué avec succès ! Commande enregistrée dans l\'historique.');

    return $this->redirectToRoute('historique_commandes');
}

    
    
    
        
#[Route('/commande/historique', name: 'historique_commandes')]
public function historiqueCommandes(EntityManagerInterface $entityManager, Security $security): Response
{
    $user = $security->getUser();

    if (!$user) {
        $this->addFlash('error', '⚠️ Vous devez être connecté pour voir votre historique.');
        return $this->redirectToRoute('app_login');
    }

    if (in_array('ROLE_ADMIN', $user->getRoles())) {
        return $this->redirectToRoute('admin_commandes'); 
    }

    $historique = $entityManager->getRepository(CommandeFinalisee::class)->findBy(
        ['user' => $user], 
        ['dateCommande' => 'DESC']
    );

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

    
#[Route('/produit/ajouter-au-panier/{id}/{quantite}', name: 'ajouter_commande', methods: ['POST'])]
public function ajouterAuPanier(Produit $produit, int $quantite, EntityManagerInterface $entityManager, CommandeRepository $commandeRepository): JsonResponse
{
    try {
        
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(["message" => "❌ Utilisateur non connecté"], Response::HTTP_FORBIDDEN);
        }

        
        if (!$produit) {
            return new JsonResponse(["message" => "❌ Produit non trouvé"], Response::HTTP_NOT_FOUND);
        }

        
        if ($quantite < 1) {
            return new JsonResponse(["message" => "❌ Quantité invalide"], Response::HTTP_BAD_REQUEST);
        }

        
        $commandeExistante = $commandeRepository->findOneBy([
            'produit' => $produit,
            'user' => $user 
        ]);

        if ($commandeExistante) {
            
            $commandeExistante->setQuantite($commandeExistante->getQuantite() + $quantite);
        } else {
            
            $commande = new Commande();
            $commande->setProduit($produit);
            $commande->setQuantite($quantite);
            $commande->setUser($user); 

            $entityManager->persist($commande);
        }

        $entityManager->flush();

        return new JsonResponse(["message" => "✅ Produit ajouté au panier"], Response::HTTP_OK);

    } catch (\Exception $e) {
        return new JsonResponse([
            "message" => "❌ Erreur : " . $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
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
    #[Route('/commande/historique/supprimer/{id}', name: 'supprimer_commande_historique')]
public function supprimerCommandeHistorique(EntityManagerInterface $entityManager, CommandeFinalisee $commandeFinalisee): Response
{
    $entityManager->remove($commandeFinalisee);
    $entityManager->flush();

    $this->addFlash('success', 'Commande supprimée de l’historique ✅');
    return $this->redirectToRoute('historique_commandes');
}
#[Route('/api/cart/count', name: 'cart_count', methods: ['GET'])]
public function getCartCount(CommandeRepository $commandeRepository): JsonResponse
{
    $commandes = $commandeRepository->findAll();
    $totalProduits = array_reduce($commandes, function ($sum, $commande) {
        return $sum + $commande->getQuantite();
    }, 0);

    return new JsonResponse(['count' => $totalProduits]);
}

}
