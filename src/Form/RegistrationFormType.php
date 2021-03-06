<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname',TextType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>15])],
                'required'   => true,
            ])
            ->add('lastname',TextType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>15])],
                'required'   => true,
            ])
            ->add('gender', ChoiceType::class, [
                'choices'  => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                    'Other' => 'Other',
                ],
            ])
            ->add('tel',TelType::class,[
                'required'   => false,
            ])
            ->add('BirthDay',BirthdayType::class,[
                'required'   => true,
            ])
            ->add('ProfilePhoto', FileType::class, [
                'label' => 'Profile Photo',
                'mapped' => false,
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '5120k',
                        'maxSizeMessage' => 'Size Too Large Max 5MB',
                        'mimeTypes' => [
                            'image/jpeg',
                            'application/png',
                            'application/x-png',
                        ],
                        'mimeTypesMessage' => 'Please upload An Image',
                    ])
                ],
            ])
            ->add('email', EmailType::class)
            ->add('plainPassword', RepeatedType::class, array(
                'mapped'=>false,
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
                'constraints' => [new Length(['min' =>8,'max' =>200])],
                'required'   => true,
            ))
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('Done',SubmitType::class,[
             'attr'=>[
                'class'=>'btn btn-success',
             ]
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
