<?php
namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la catégorie',
                'attr' => [
                    'placeholder' => 'Entrez le nom de la catégorie',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom de la catégorie est obligatoire.',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Le nom ne doit pas dépasser 255 caractères.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez une description',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La  description de la catégorie est obligatoire.',
                    ]),
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne doit pas dépasser 1000 caractères.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
