<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Generate a token (similar to ApiLoginController)
        $token = bin2hex(random_bytes(32));

        // Create a response object
        $response = $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'token' => $token,  // Pass the token to the template
        ]);

        // Add the token to the response headers
        $response->headers->set('X-Auth-Token', $token);

        return $response;
    }
}