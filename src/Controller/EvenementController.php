<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\EvenementRepository;
use App\Entity\Inscription;
use App\Form\InscriptionType; 
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\CommentaireEvent;
use App\Form\CommentaireEventType;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

final class EvenementController extends AbstractController
{
    #[Route('/evenement/inscription/{id}', name: 'app_evenement_inscription_form')]
    public function inscriptionForm(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
    
        // Créez une nouvelle inscription
        $inscription = new Inscription();
        $inscription->setEvenement($evenement);
    
        // Si l'utilisateur est connecté, pré-remplissez les champs
        if ($user) {
            $inscription->setNom($user->getNom());
            $inscription->setPrenom($user->getPrenom());
            $inscription->setEmail($user->getEmail());
            $inscription->setNumTel($user->getNumTel());
        }
    
        // Créez le formulaire
        $form = $this->createForm(InscriptionType::class, $inscription);
    
        // Traitez la soumission du formulaire
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Associez l'utilisateur connecté à l'inscription
            if ($user) {
                $inscription->setUser($user);
            }
    
            // Enregistrez l'inscription en base de données
            $entityManager->persist($inscription);
            $entityManager->flush();
    
            $this->addFlash('success', 'Inscription réussie !');
            return $this->redirectToRoute('app_evenements_liste'); // Redirection vers la liste des événements
        }
    
        // Affichez le formulaire
        return $this->render('evenement/inscription_form.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
            'user' => $user, // Passez l'utilisateur connecté au template
        ]);
    }
//     #[Route('/evenement/ajout', name: 'app_evenement_ajout')]
// public function ajout(Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
// {
//     // Crée une nouvelle instance de l'entité Evenement
//     $evenement = new Evenement();

//     // Crée le formulaire
//     $form = $this->createForm(EvenementType::class, $evenement);
//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         // Gestion de la photo
//         $photoFile = $form->get('photoFile')->getData();
//         if ($photoFile) {
//             try {
//                 // Génère un nom de fichier unique
//                 $photoFilename = uniqid() . '.' . $photoFile->guessExtension();

//                 // Déplace le fichier téléchargé vers le répertoire de stockage
//                 $photoFile->move(
//                     $params->get('photos_directory'),
//                     $photoFilename
//                 );

//                 // Associe le nom du fichier à l'entité Evenement
//                 $evenement->setPhoto($photoFilename);
//             } catch (\Exception $e) {
//                 $this->addFlash('error', 'Erreur lors du téléchargement de la photo.');
//                 return $this->redirectToRoute('app_evenement_ajout');
//             }
//         }

//         // Gestion des régions
//         $regions = $form->get('regions')->getData();
//         foreach ($regions as $region) {
//             $evenement->addRegion($region);
//         }

//         // Enregistre l'événement en base de données
//         $entityManager->persist($evenement);
//         $entityManager->flush();

//         // Ajoute un message de succès
//         $this->addFlash('success', 'Événement ajouté avec succès !');

//         // Redirige l'utilisateur vers la liste des événements
//         return $this->redirectToRoute('app_evenement');
//     }

//     // Affiche le formulaire
//     return $this->render('evenement/ajout.html.twig', [
//         'form' => $form->createView(),
//     ]);
// }
private function createGoogleClient(): Client
    {
        $client = new Client();
        $serviceAccountFilePath = $this->getParameter('google_service_account_path');

        if (!file_exists($serviceAccountFilePath)) {
            throw new \Exception("Service account file not found at: " . $serviceAccountFilePath);
        }

        $client->setAuthConfig($serviceAccountFilePath);
        $client->addScope(Calendar::CALENDAR);
        $client->setSubject('oussema@emerald-metrics-452814-f4.iam.gserviceaccount.com');

        return $client;
    }

    #[Route('/evenement/ajout', name: 'app_evenement_ajout')]
    public function ajout(Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de la photo
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                try {
                    $photoFilename = uniqid() . '.' . $photoFile->guessExtension();
                    $photoFile->move(
                        $params->get('photos_directory'),
                        $photoFilename
                    );
                    $evenement->setPhoto($photoFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo.');
                    return $this->redirectToRoute('app_evenement_ajout');
                }
            }

            // Gestion des régions
            $regions = $form->get('regions')->getData();
            foreach ($regions as $region) {
                $evenement->addRegion($region);
            }

            // Enregistre l'événement en base de données
            $entityManager->persist($evenement);
            $entityManager->flush();

            // Add to Google Calendar
            try {
                $client = $this->createGoogleClient();
                $service = new Calendar($client);
                $calendarId = 'primary';

                $googleEvent = new Event([
                    'summary' => $evenement->getTitre(),
                    'description' => $evenement->getDescription(),
                    'start' => [
                        'dateTime' => $evenement->getDateDebut()->format('c'),
                        'timeZone' => 'UTC',
                    ],
                    'end' => [
                        'dateTime' => $evenement->getDateFin()->format('c'),
                        'timeZone' => 'UTC',
                    ],
                    'extendedProperties' => [
                        'private' => [
                            'localEventId' => $evenement->getId() // Store local ID for reference
                        ]
                    ]
                ]);

                $createdEvent = $service->events->insert($calendarId, $googleEvent);
                // You might want to store $createdEvent->getId() in your Evenement entity
                // if you add a googleEventId field to track the Google Calendar event ID
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Événement ajouté localement mais erreur avec Google Calendar: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Événement ajouté avec succès !');
            return $this->redirectToRoute('app_evenement');
        }

        return $this->render('evenement/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/evenement/supprimer/{id}', name: 'app_evenement_supprimer')]
    public function supprimer(Evenement $evenement, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        // Supprimer le fichier de la photo si elle existe
        $filesystem = new Filesystem();
        $photoPath = $params->get('photos_directory') . '/' . $evenement->getPhoto();
        if ($filesystem->exists($photoPath)) {
            $filesystem->remove($photoPath);
        }

        // Remove from Google Calendar
        try {
            $client = $this->createGoogleClient();
            $service = new Calendar($client);
            $calendarId = 'primary';
            
            // Note: This assumes you have a way to get the Google event ID
            // Ideally, you'd store this ID when creating the event
            // For this example, we'll search for the event by local ID
            $events = $service->events->listEvents($calendarId, [
                'privateExtendedProperty' => 'localEventId=' . $evenement->getId()
            ]);

            if ($eventItems = $events->getItems()) {
                $googleEventId = $eventItems[0]->getId();
                $service->events->delete($calendarId, $googleEventId);
            }
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Événement supprimé localement mais erreur avec Google Calendar: ' . $e->getMessage());
        }

        $entityManager->remove($evenement);
        $entityManager->flush();
        $this->addFlash('success', 'Événement supprimé avec succès.');

        return $this->redirectToRoute('app_evenement');
    }






    #[Route('/evenement/{id}', name: 'app_evenement_voir')]
    public function voir(int $id, EvenementRepository $evenementRepository): Response
    {
        $evenement = $evenementRepository->find($id);
        if (!$evenement) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }
        return $this->render('evenement/voir.html.twig', [
            'evenement' => $evenement,
        ]);
    }



    // Modifier un événement avec gestion de la photo
    #[Route('/evenement/modifier/{id}', name: 'app_evenement_modifier')]
    public function modifier(Request $request, Evenement $evenement, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photoFile')->getData();

            if ($photoFile) {
                $photoFilename = uniqid() . '.' . $photoFile->guessExtension();
                $photoFile->move(
                    $params->get('photos_directory'),
                    $photoFilename
                );
                $evenement->setPhoto($photoFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Événement modifié avec succès.');

            return $this->redirectToRoute('app_evenement');
        }

        return $this->render('evenement/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // // Supprimer un événement et la photo associée
    // #[Route('/evenement/supprimer/{id}', name: 'app_evenement_supprimer')]
    // public function supprimer(Evenement $evenement, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    // {
    //     // Supprimer le fichier de la photo si elle existe
    //     $filesystem = new Filesystem();
    //     $photoPath = $params->get('photos_directory') . '/' . $evenement->getPhoto();
    //     if ($filesystem->exists($photoPath)) {
    //         $filesystem->remove($photoPath);
    //     }

    //     $entityManager->remove($evenement);
    //     $entityManager->flush();
    //     $this->addFlash('success', 'Événement supprimé avec succès.');

    //     return $this->redirectToRoute('app_evenement');
    // }











//liste (front) 
#[Route('/evenements/liste', name: 'app_evenements_liste')]
public function listEvenements(Request $request, EvenementRepository $evenementRepository, PaginatorInterface $paginator): Response
{
    // Récupérer le terme de recherche depuis la requête
    $searchTerm = $request->query->get('search', '');

    // Récupérer le paramètre de tri depuis la requête
    $sortBy = $request->query->get('sort_by', 'default');

    // Filtrer les événements en fonction du terme de recherche et du tri
    $query = $evenementRepository->findBySearchAndSort($searchTerm, $sortBy);

    // Paginer les résultats
    $evenements = $paginator->paginate(
        $query, // Requête à paginer
        $request->query->getInt('page', 1), // Numéro de la page, 1 par défaut
        10 // Nombre d'éléments par page
    );

    return $this->render('evenement/liste.html.twig', [
        'evenements' => $evenements,
        'searchTerm' => $searchTerm, // Passer le terme de recherche au template
        'sortBy' => $sortBy, // Passer le paramètre de tri au template
    ]);
}




    // Détail d'un événement(front)
    #[Route('/evenements/{id}', name: 'detail_evenement', methods: ['GET', 'POST'])]
    public function detail(int $id, EvenementRepository $evenementRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'événement par son ID
        $evenement = $evenementRepository->find($id);
    
        // Vérifier si l'événement existe
        if (!$evenement) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }
    
        // Créer un nouveau commentaire et le formulaire associé
        $commentaire = new CommentaireEvent();
        $form = $this->createForm(CommentaireEventType::class, $commentaire);
    
        // Gérer la soumission du formulaire
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Associer le commentaire à l'événement et à l'utilisateur connecté
            $commentaire->setEvenement($evenement);
            $commentaire->setUser($this->getUser()); // Assurez-vous que l'utilisateur est connecté
            $commentaire->setDateCreation(new \DateTime());
    
            // Enregistrer le commentaire en base de données
            $entityManager->persist($commentaire);
            $entityManager->flush();
    
            // Rediriger pour éviter la soumission multiple du formulaire
            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès !');
            return $this->redirectToRoute('detail_evenement', ['id' => $id]);
        }
    
        // Afficher la vue avec l'événement, le formulaire et les commentaires existants
        return $this->render('evenement/detail.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(), // Passer le formulaire à la vue
            'commentaires' => $evenement->getCommentaireEvents(), // Passer les commentaires existants
        ]);
    }

    
        
    //inscription
    
    #[Route('/evenement', name: 'app_evenement')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $evenements = $entityManager->getRepository(Evenement::class)->findAll();

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,  
        ]);
    }
}