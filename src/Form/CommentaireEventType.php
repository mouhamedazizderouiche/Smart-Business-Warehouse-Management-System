<?php

namespace App\Form;

use App\Entity\CommentaireEvent;
use App\Entity\Evenement;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CommentaireEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Contenu field for the comment text (required)
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu du commentaire',
                'attr' => [
                    'placeholder' => 'Écrivez votre commentaire ici...',
                    'rows' => 5,
                ],
            ])

        //     // Evenement field for selecting the related event (dropdown)
        //     ->add('evenement', EntityType::class, [
        //         'class' => Evenement::class,
        //         'choice_label' => 'titre', // assuming 'titre' is the property you want to display
        //         'label' => 'Événement associé',
        //         'placeholder' => 'Sélectionnez un événement',
        //         'required' => true,
        //     ])

        //     // User field for associating a user to the comment (dropdown, optional)
        //     ->add('user', EntityType::class, [
        //         'class' => User::class,
        //         'choice_label' => 'nom', // assuming you want to display user's name
        //         'label' => 'Utilisateur',
        //         'placeholder' => 'Sélectionnez un utilisateur',
        //         'required' => true,
        //     ])
        // ;
    ;}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommentaireEvent::class, // Bind this form to the CommentaireEvent entity
        ]);
    }
}
