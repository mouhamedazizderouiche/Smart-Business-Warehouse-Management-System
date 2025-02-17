<?php

namespace App\Controller;
use App\Entity\Reclamations;
use Doctrine\ORM\EntityManagerInterface;
use StatutReclamation; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $reclamations = $entityManager->getRepository(Reclamations::class)->findBy([
            'statut' => StatutReclamation::AVIS
        ]);

        return $this->render('homepage/homepage.html.twig', [
            'reclamationsAvis' => $reclamations
        ]);
    }
    
}
