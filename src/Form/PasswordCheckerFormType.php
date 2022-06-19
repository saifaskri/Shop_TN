<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordCheckerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword',PasswordType::class,[
                'mapped'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Write Your Password ....',
                    'class'=>'mt-2'
                ]
            ])
            ->add('Send',SubmitType::class,[
                'attr'=>[
                    'class'=>'btn btn-warning',
                ]
            ])
            ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

        ]);
    }
}
