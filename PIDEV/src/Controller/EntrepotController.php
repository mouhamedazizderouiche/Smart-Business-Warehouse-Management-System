<?php
// src/Controller/EntrepotController.php
namespace App\Controller;

use App\Entity\Entrepot;
use App\Form\EntrepotType;
use App\Repository\EntrepotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/entrepot')]
class EntrepotController extends AbstractController
{
    #[Route('/', name: 'entrepot_index', methods: ['GET'])]
    public function index(EntrepotRepository $entrepotRepository): Response
    {
        return $this->render('entrepot/index.html.twig', [
            'entrepots' => $entrepotRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'entrepot_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entrepot = new Entrepot();
        $form = $this->createForm(EntrepotType::class, $entrepot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entrepot);
            $entityManager->flush();

            $this->addFlash('success', 'Entrepôt créé avec succès.');
            return $this->redirectToRoute('entrepot_index');
        }

        return $this->render('entrepot/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'entrepot_show', methods: ['GET'])]
    public function show(Entrepot $entrepot): Response
    {
        return $this->render('entrepot/show.html.twig', [
            'entrepot' => $entrepot,
        ]);
    }

    #[Route('/{id}/edit', name: 'entrepot_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entrepot $entrepot, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntrepotType::class, $entrepot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Entrepôt modifié avec succès.');
            return $this->redirectToRoute('entrepot_index');
        }

        return $this->render('entrepot/edit.html.twig', [
            'form' => $form->createView(),
            'entrepot' => $entrepot,
        ]);
    }

    #[Route('/{id}/delete', name: 'entrepot_delete', methods: ['POST'])]
    public function delete(Request $request, Entrepot $entrepot, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $entrepot->getId(), $request->request->get('_token'))) {
            $entityManager->remove($entrepot);
            $entityManager->flush();

            $this->addFlash('success', 'Entrepôt supprimé avec succès.');
        }

        return $this->redirectToRoute('entrepot_index');
    }
}