<?php
namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'empty_data' => '',
                'label' => 'Nom du produit',
                'constraints' => [
                   
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s]+$/u',
                        'message' => 'Le nom ne doit contenir que des lettres et des espaces.',
                    ]),
                ],
            ])
            ->add('prixUnitaire', NumberType::class, [
                'label' => 'Prix Unitaire',
                'empty_data' => '0',
                'constraints' => [
                   
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'Le prix doit être un nombre valide.',
                    ]),
                ],
                'invalid_message' => 'Veuillez entrer un prix valide.',
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité en stock',
                'empty_data' => '0',
                'constraints' => [
                    new Assert\Positive(['message' => 'La quantité ne peut pas être négative.']),
                ],
                'attr' => ['min' => 0],
                'invalid_message' => 'Veuillez entrer un nombre entier valide.',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'empty_data' => '',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('urlImageProduit', FileType::class, [
                'label' => 'URL de l\'image',
                'mapped' => false,
                'required' => !$options['is_edit'],
                'attr' => ['accept' => 'image/*'],
                
            ])
            ->add('categorie', EntityType::class, [
        'class' => Categorie::class,
        'empty_data' => '',
        'choice_label' => 'nom',
        'label' => 'Catégorie',
        'placeholder' => 'Sélectionner une catégorie',
        'constraints' => [
            new Assert\NotNull(['message' => 'Veuillez sélectionner une catégorie.']),
        ],
    ])
        ->add('save', SubmitType::class, [
            'label' => 'Soumettre',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
            'is_edit' => false,
        ]);
    }
}
