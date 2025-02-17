<?php

namespace App\Form;

use App\Entity\Stock;
use App\Entity\Produit;
use App\Entity\Fournisseur;
use App\Entity\Entrepot; // Importez l'entité Entrepot
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
            ])
            ->add('entrepots', EntityType::class, [
                'class' => Entrepot::class, // Entité Entrepot
                'choice_label' => 'nom', // Champ à afficher dans la liste déroulante
                'multiple' => true, // Permet de sélectionner plusieurs entrepôts
                'expanded' => false, // Affiche une liste déroulante
                'label' => 'Entrepôts',
                'required' => false, // Optionnel
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}