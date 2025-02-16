<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Form\StockType;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/stock')]
class StockController extends AbstractController
{
    #[Route('/', name: 'app_stock_index', methods: ['GET'])]
    public function index(StockRepository $stockRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $stockRepository->createQueryBuilder('s')->getQuery();
        $stocks = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('stock/index.html.twig', [
            'stocks' => $stocks,
        ]);
    }

    #[Route('/new', name: 'app_stock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stock = new Stock();
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stock);
            $entityManager->flush();

            $this->addFlash('success', 'Stock créé avec succès.');
            return $this->redirectToRoute('app_stock_index');
        }

        return $this->render('stock/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stock $stock, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Stock mis à jour avec succès.');
            return $this->redirectToRoute('app_stock_index');
        }

        return $this->render('stock/edit.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Mettre à jour',
        ]);
    }

    #[Route('/{id}', name: 'app_stock_delete', methods: ['POST'])]
    public function delete(Request $request, Stock $stock, EntityManagerInterface $entityManager): Response
    {
        if (!$stock) {
            $this->addFlash('error', 'Stock introuvable.');
            return $this->redirectToRoute('app_stock_index');
        }

        if ($this->isCsrfTokenValid('delete'.$stock->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($stock);
                $entityManager->flush();
                $this->addFlash('success', 'Stock supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du stock.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_stock_index');
    }
}