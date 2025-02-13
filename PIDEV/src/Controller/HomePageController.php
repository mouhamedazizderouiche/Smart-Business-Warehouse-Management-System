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
        $this->denyAccessUnlessGranted('ROLE_USER');


        return $this->render('homepage/homepage.html.twig');
    }
    
    #[Route('/shop', name: 'shop')]
    public function shop(): Response
    {
        return $this->render('homepage/shop.html.twig');
    }

    #[Route('/shop/details', name: 'shopdetails')]
    public function details(): Response
    {
        return $this->render('homepage/shop-details.html.twig');
    }
    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('homepage/contact.html.twig');
    }
    #[Route('/cart', name: 'cart')]
    public function cart(): Response
    {
        return $this->render('homepage/cart.html.twig');
    }
    #[Route('/checkout', name: 'checkout')]
    public function checkout(): Response
    {
        return $this->render('homepage/checkout.html.twig');
    }
    #[Route('/testimonial', name: 'testimonial')]
    public function testimonial(): Response
    {
        return $this->render('homepage/testimonial.html.twig');
    }
    




}