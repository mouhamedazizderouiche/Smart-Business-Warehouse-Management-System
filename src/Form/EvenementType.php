<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Region;
use App\Enum\TypeEvenement;
use App\Enum\StatutEvenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'événement',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'événement',
                'choices' => [
                    'Foire' => TypeEvenement::FOIRE,
                    'Formation' => TypeEvenement::FORMATION,
                    'Conférence' => TypeEvenement::CONFERENCE,
                ],
                'choice_label' => fn($choice) => $choice->label(),
                'choice_value' => fn(?TypeEvenement $enum) => $enum?->value,
                'placeholder' => 'Sélectionnez un type',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut de l\'événement',
                'choices' => [
                    'À venir' => StatutEvenement::A_VENIR,
                    'Annulé' => StatutEvenement::ANNULE,
                    'Terminé' => StatutEvenement::TERMINE,
                ],
                'choice_label' => fn($choice) => $choice->label(),
                'choice_value' => fn(?StatutEvenement $enum) => $enum?->value,
                'placeholder' => 'Sélectionnez un statut',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'input' => 'datetime',
                'required' => true,
                'attr' => ['class' => 'form-control', 'type' => 'datetime-local'],
            ])
            ->add('dateFin', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'input' => 'datetime',
                'required' => true,
                'attr' => ['class' => 'form-control', 'type' => 'datetime-local'],
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo de l\'événement',
                'required' => false,
                'mapped' => false,
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF).',
                    ]),
                ],
            ])
            ->add('regions', EntityType::class, [
                'label' => 'Régions',
                'class' => Region::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true, // ou true pour des cases à cocher
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}