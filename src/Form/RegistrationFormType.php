<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\File;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom de famille.']),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'Votre nom de famille ne doit contenir que des lettres et des espaces.',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre prénom.']),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'Votre prénom ne doit contenir que des lettres et des espaces.',
                    ]),
                ],
            ])
            ->add('email', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse email.']),
                    new Email(['message' => 'Veuillez entrer une adresse email valide.']),
                ],
            ])
            ->add('travail', ChoiceType::class, [
                'choices' => [
                    'Agriculteur' => 'agriculteur',
                    'Fournisseur' => 'fournisseur',
                    'Organisateur' => 'organisateur',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner votre rôle.']),
                ],
            ])
            ->add('numTel', TelType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre numéro de téléphone.']),
                    new Length(['min' => 8, 'max' => 15, 'minMessage' => 'Le numéro de téléphone doit contenir au moins {{ limit }} chiffres.', 'maxMessage' => 'Le numéro de téléphone ne doit pas dépasser {{ limit }} chiffres.']),
                    new Regex([
                        'pattern' => '/^[0-9]+$/',
                        'message' => 'Le numéro de téléphone ne doit contenir que des chiffres.',
                    ]),
                ],
            ])
            ->add('photoUrl', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false, // Ce champ n'est pas mappé à l'entité
                'required' => false, // Le fichier est facultatif
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier image valide (JPEG, PNG, GIF).',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    new Length(['min' => 6, 'max' => 4096, 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.']),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(['message' => 'Vous devez accepter nos conditions.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}