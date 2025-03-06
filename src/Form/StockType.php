<?php

namespace App\Form;

use App\Entity\Entrepot;
use App\Entity\Fournisseur;
use App\Entity\Produit;
use App\Entity\Stock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class StockType extends AbstractType
{
// src/Form/StockType.php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
    ->add('produit', EntityType::class, [
      'class' => Produit::class, // Assurez-vous que c'est la bonne entité
      'choice_label' => 'nom', // Assurez-vous que 'nom' est une propriété valide de l'entité Produit
      'placeholder' => 'Choisir un produit', // Texte par défaut
      'attr' => ['class' => 'form-control'], // Classes CSS
  ])
        ->add('dateEntree', DateTimeType::class, [
            'widget' => 'single_text',
            'data' => new \DateTime(), // Définit la valeur par défaut à maintenant
            'constraints' => [
                new NotBlank(['message' => 'La date d\'entrée est obligatoire.']),
            ],
            'attr' => ['class' => 'form-control']
        ])
        ->add('dateSortie', DateTimeType::class, [
            'widget' => 'single_text',
            'required' => false,
            
        ])
        ->add('fournisseurs', EntityType::class, [
            'class' => Fournisseur::class,
            'choice_label' => 'nom',
            'multiple' => true,
            'expanded' => true,
            'constraints' => [
                new NotBlank(['message' => 'Vous devez sélectionner au moins un fournisseur.']),
            ],
            'attr' => ['class' => 'form-control']
        ])
        ->add('seuilAlert', IntegerType::class, [
            'label' => 'Seuil d\'alerte',
            'required' => false,
            'constraints' => [
                new Positive(['message' => 'Le seuil d\'alerte doit être un nombre positif.']),
                new NotBlank(['message' => 'Le seuil d\'alerte est obligatoire.']),
            ],
            'attr' => ['class' => 'form-control']
        ])
        ->add('entrepots', EntityType::class, [
            'class' => Entrepot::class,
            'choice_label' => 'nom',
            'multiple' => true,
            'expanded' => true,
            'constraints' => [
                new NotBlank(['message' => 'Vous devez sélectionner au moins un entrepôt.']),
            ],
            'attr' => ['class' => 'form-control']
        ]);
}

public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Stock::class,
        'produits' => [], // Définir l'option 'produits' avec une valeur par défaut
    ]);
}
}