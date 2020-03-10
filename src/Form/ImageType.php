<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Siparis;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('siparis')
            ->add('image',FileType::class,[
                'data_class' => null,
                'label'=>'Image İmage',
                'mapped'=>'False',
                'required'=>'False',
                'constraints'=>[
                    new File([
                        'maxSize'=>'1024k',
                        'mimeTypes'=>[
                            'image/*',
                        ],
                        'mimeTypesMessage'=>'Please upload am İmage File',
                    ])
                ],

            ])
            ->add('siparis',EntityType::class,[
                'class'=>Siparis::class,
                'choice_label'=>'title'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'csrf_protection'=>false

        ]);
    }
}
