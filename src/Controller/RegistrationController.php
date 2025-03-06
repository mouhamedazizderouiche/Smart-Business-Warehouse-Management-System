<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier, private LoggerInterface $logger)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setIsVerified(true);
            $user->setDateIscri(new \DateTime());

            // Gestion de l'image faciale et récupération du face_token
            $faceImageFile = $form->get('faceImage')->getData();
            if ($faceImageFile) {
                $faceImageBase64 = base64_encode(file_get_contents($faceImageFile->getPathname()));
                $faceToken = $this->detectFaceAndGetToken($faceImageBase64);
                if ($faceToken) {
                    $user->setFaceToken($faceToken);
                } else {
                    $this->addFlash('error', 'Face detection failed. Please try another image.');
                    return $this->redirectToRoute('app_register');
                }
            }

            // Encodage du mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi de l'email de confirmation
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@agriplanner.com', 'No Reply'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Registration successful! Please check your email to verify your account.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    private function detectFaceAndGetToken(string $imageBase64): ?string
    {
        try {
            $client = new Client();
            $response = $client->post('https://api-us.faceplusplus.com/facepp/v3/detect', [
                'form_params' => [
                    'api_key' => $_ENV['FACEPLUSPLUS_API_KEY'],
                    'api_secret' => $_ENV['FACEPLUSPLUS_API_SECRET'],
                    'image_base64' => $imageBase64,
                    'return_attributes' => 'none',
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (!empty($result['faces'])) {
                return $result['faces'][0]['face_token'];
            } else {
                // Log the error and return null
                $this->logger->error('No face detected in the provided image.');
                $this->addFlash('error', 'No face detected. Please try again with a different image.');
            }
        } catch (\Exception $e) {
            // Log the error
            $this->logger->error('Face detection API error: ' . $e->getMessage());
            $this->addFlash('error', 'There was an error with face detection. Please try again.');
        }

        return null;
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');
        return $this->redirectToRoute('app_login');
    }
}
