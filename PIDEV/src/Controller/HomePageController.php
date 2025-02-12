<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {

        return $this->render('homepage/homepage.html.twig');
    }
    #[Route('/produit', name: 'produit')]
    public function produit(): Response
    {
        return $this->render('produit/produit.html.twig');
    }
    #[Route('/commande', name: 'commande')]
    public function commande(): Response
    {
        return $this->render('commande/commande.html.twig');
    }
    




}