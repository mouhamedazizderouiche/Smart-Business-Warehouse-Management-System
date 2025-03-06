<?php

namespace App\Form;

use App\Entity\Inscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire.']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre nom',
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire.']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre prénom',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire.']),
                    new Email(['message' => 'Veuillez entrer un email valide.']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre email',
                ],
            ])
            ->add('num_tel', TextType::class, [
                'label' => 'Numéro de téléphone',
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est obligatoire.']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre numéro de téléphone',
                ],
            ])
            // Changed 'role' to 'travail' to reflect occupation/work type
            ->add('travail', ChoiceType::class, [
                'label' => 'Travail',
                'choices' => [
                    'Agriculteur' => 'AGRICULTEUR',
                    'Fournisseur' => 'FOURNISSEUR',
                ],
                'expanded' => true, // Display as radio buttons
                'multiple' => false, // Single choice
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir un travail.']),
                ],
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
            // Optionally disable HTML5 validation to rely only on Symfony's constraints
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
