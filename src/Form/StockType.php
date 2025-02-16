<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Produit;
use App\Entity\Fournisseur;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
            ])
            ->add('quantite', IntegerType::class)
            ->add('dateEntree', DateTimeType::class) // Utilisez dateEntree au lieu de date_entree
            ->add('dateSortie', DateTimeType::class, [
                'required' => false,
            ])
            ->add('fournisseurs', EntityType::class, [
                'class' => Fournisseur::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}