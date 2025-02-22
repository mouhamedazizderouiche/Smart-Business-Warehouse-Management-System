<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface; // Make sure to import this

class SecurityController extends AbstractController
{
    private $authenticationUtils;
    private $logger; // Declare the logger

    // Injecting the LoggerInterface into the constructor
    public function __construct(AuthenticationUtils $authenticationUtils, LoggerInterface $logger)
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->logger = $logger; // Assigning logger to class property
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/face-authentication', name: 'face_authentication')]
    public function faceAuthentication(): Response
    {
        return $this->render('security/face_authentication.html.twig');
    }

    #[Route('/verify-face', name: 'verify_face', methods: ['POST'])]
    public function verifyFace(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $imageBase64 = $data['image'] ?? null;
        $user = $this->getUser();

        if (!$imageBase64 || !$user) {
            return new JsonResponse(['success' => false, 'message' => 'No image provided or user not authenticated.']);
        }

        try {
            $client = new Client();

            // Step 1: Detect face in the uploaded image
            $response = $client->post('https://api-us.faceplusplus.com/facepp/v3/detect', [
                'form_params' => [
                    'api_key' => $_ENV['FACEPLUSPLUS_API_KEY'],
                    'api_secret' => $_ENV['FACEPLUSPLUS_API_SECRET'],
                    'image_base64' => $imageBase64,
                    'return_attributes' => 'none',
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (empty($result['faces'])) {
                return new JsonResponse(['success' => false, 'message' => 'No face detected.']);
            }

            $detectedFaceToken = $result['faces'][0]['face_token'];

            // Step 2: Compare with the user's saved face_token
            $responseCompare = $client->post('https://api-us.faceplusplus.com/facepp/v3/compare', [
                'form_params' => [
                    'api_key' => $_ENV['FACEPLUSPLUS_API_KEY'],
                    'api_secret' => $_ENV['FACEPLUSPLUS_API_SECRET'],
                    'face_token1' => $user->getFaceToken(),
                    'face_token2' => $detectedFaceToken,
                ],
            ]);

            $compareResult = json_decode($responseCompare->getBody()->getContents(), true);

            // Log the Face++ response for debugging
            $this->logger->info('Face++ Response: ' . json_encode($compareResult));

            // Check the confidence score and compare it to the threshold
            if (!empty($compareResult['confidence']) && $compareResult['confidence'] >= 60) { // Confidence threshold set to 75
                return new JsonResponse(['success' => true, 'message' => 'Face recognized successfully.']);
            } else {
                return new JsonResponse(['success' => false, 'message' => 'Face not recognized.']);
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            $this->logger->error('Error occurred while verifying face: ' . $e->getMessage());
            return new JsonResponse(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}
