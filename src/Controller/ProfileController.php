<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\User1Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $user = $this->getUser();
        return $this->render('profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser(); // Get the currently logged-in user

        // Create the form based on the User entity
        $form = $this->createForm(User1Type::class, $user);
        $form->handleRequest($request);

        // If the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload for profile photo
            $photo = $form->get('photo')->getData();

            if ($photo) {
                // Create a unique filename for the uploaded file
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename); // Slugify the original filename
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();

                // Try to move the uploaded file to the directory
                try {
                    $photo->move(
                        $this->getParameter('uploads_directory'), // You need to configure this in services.yaml
                        $newFilename
                    );
                    $user->setPhotoUrl($newFilename); // Save the filename in the User entity
                } catch (FileException $e) {
                    // Handle the error (optional)
                    $this->addFlash('error', 'Failed to upload photo');
                }
            }

            // Save the changes in the database
            $entityManager->flush();

            // Redirect to the profile page after saving
            return $this->redirectToRoute('app_profile_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
