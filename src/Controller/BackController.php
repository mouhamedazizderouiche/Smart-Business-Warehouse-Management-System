<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BackController extends AbstractController{
    #[Route('/sidebar', name: 'app_side')]
    public function index(): Response
    {
        return $this->render('backoffice/sidebar.html.twig', [
            'controller_name' => 'BackController',
        ]);
    }
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('backoffice/dashboard.html.twig', [
            'controller_name' => 'BackController',
        ]);
    }

   
}