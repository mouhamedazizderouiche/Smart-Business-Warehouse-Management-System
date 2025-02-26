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
use App\Entity\Fournisseur;
use App\Entity\Entrepot;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


#[Route('/stock')]
class StockController extends AbstractController
{
    #[Route('/', name: 'stock_index')]
    public function index(StockRepository $stockRepository): Response
    {
        return $this->render('stock/index.html.twig', [
            'stocks' => $stockRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'stock_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $stock = new Stock();
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($stock);
            $em->flush();

            $this->addFlash('success', 'Le stock a été créé avec succès.');
            return $this->redirectToRoute('stock_index');
        }

        return $this->render('stock/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

#[Route('/{id}/edit', name: 'stock_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Stock $stock, EntityManagerInterface $em): Response
{
    $form = $this->createFormBuilder($stock)
        ->add('quantite', IntegerType::class, [
            'label' => 'Quantité',
        ])
        ->add('entrepot', EntityType::class, [
            'class' => Entrepot::class,
            'choice_label' => 'nom',
            'label' => 'Entrepôt'
        ])
        ->add('fournisseurs', EntityType::class, [
            'class' => Fournisseur::class,
            'multiple' => true,
            'expanded' => false,
            'choice_label' => 'nom',
            'label' => 'Fournisseurs'
        ])
        ->add('save', SubmitType::class, [
            'label' => 'Enregistrer',
            'attr' => ['class' => 'btn btn-primary']
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Mettre à jour la date système automatiquement
        $stock->setDateMiseAjour(new \DateTime());
        
        $em->persist($stock);
        $em->flush();

        $this->addFlash('success', 'Stock mis à jour avec succès.');

        return $this->redirectToRoute('stock_index');
    }

    return $this->render('stock/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/{id}/delete', name: 'stock_delete')]
    public function delete(Stock $stock, EntityManagerInterface $em): Response
    {
        $em->remove($stock);
        $em->flush();

        $this->addFlash('success', 'Le stock a été supprimé avec succès.');
        return $this->redirectToRoute('stock_index');
    }
}
