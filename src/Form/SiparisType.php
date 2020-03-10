<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Siparis;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;




class SiparisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('category',EntityType::class,[
                'class'=>Category::class,
                'choice_label'=>'title'
    ])
            ->add('title')
            ->add('keywords')
            ->add('descriptions')
            ->add('adress')
            ->add('price')
            ->add('amount')

            ->add('image',FileType::class,[
                'data_class' => null,
                'label'=>'Siparis İmage',
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
            ->add('detail')
            ->add('status', ChoiceType::class,[
                'choices'=>[
                    'True'=>'True',
                    'False'=>'False'],
                ]
            )
            ->add('created_at')
            ->add('updated_at')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Siparis::class,
            'csrf_protection'=>false
        ]);
    }
}
