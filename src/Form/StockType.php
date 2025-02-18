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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'constraints' => [
                  new NotBlank(['message' => 'Le produit est obligatoire.']),
              ],
            ])
            ->add('quantite', IntegerType::class,[
                'constraints' => [
                    new NotBlank(['message' => 'La quantité est obligatoire.']),
                    new Positive(['message' => 'La quantité doit être un nombre positif.']),
                ], ])
            ->add('dateEntree', DateTimeType::class , [
              'constraints' => [
                  new NotBlank(['message' => 'La date d\'entrée est obligatoire.']),
              ],
          ]) 
            ->add('dateSortie', DateTimeType::class, [
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
            ])
            ->add('entrepots', EntityType::class, [
              'class' => Entrepot::class,
              'choice_label' => 'nom',
              'multiple' => true, // Permet de sélectionner plusieurs entrepôts
              'expanded' => true, // Affiche les options sous forme de cases à cocher
              'constraints' => [
                  new NotBlank(['message' => 'Vous devez sélectionner au moins un entrepôt.']),
              ],
          ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}