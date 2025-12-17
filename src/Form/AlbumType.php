<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Serie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AlbumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('titre')
        ->add('numero', IntegerType::class)
        ->add('serie', EntityType::class, [
            'class' => Serie::class,
            'choice_label' => 'nom',
        ])
        ->add('couverture', FileType::class, [
            'label' => 'Image de couverture',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                ])
            ],
        ]);

    // 👉 Ajouter le champ *seulement si on est en mode edit*
   // if ($options['is_edit']) {
   //     $builder->add('lu', CheckboxType::class, [
   //         'label' => 'Lu ?',
   //         'required' => false,
   //     ]);
   // }
}

public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Album::class,
        'is_edit' => false,   // valeur par défaut
    ]);
}
}