<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;

class UserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('FirstName',TextType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>15])],
                'required'   => true,
                'label'=>false,
                'attr'=>[
                ]
            ])

            ->add('LastName',TextType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>15])],
                'required'   => true,
                'label'=>false,
                'attr'=>[
                ]
            ])

            ->add('ProfilePhoto', FileType::class, [
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Profile Image',
                ],
                'mapped' => false,
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'maxSizeMessage' => 'Size Too Large Max 10MB',
                        // 'mimeTypes' => [
                        //     'image/jpeg',
                        //     'application/png',
                        //     'application/x-png',
                        // ],
                        // 'mimeTypesMessage' => 'Please upload An Image',
                    ])
                ],
                'help'=>'Profile Image',

            ])

            ->add('BirthDay',BirthdayType::class,[
                'required'   => true,
                'format' => 'dd MM yyyy',
                'label'=>false,
                'help'=>'BirthDay',
                'attr'=>[
                    'placeholder'=>'Birthday',
                ]


            ])

            ->add('Tel',TelType::class,[
                'required'   => false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Your Phone Number',
                ]
            ])

            ->add('gender', ChoiceType::class, [
                'choices'  => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                    'Other' => 'Other',
                ],
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Gender',
                ]
            ])

            ->add('address',TextType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>100])],
                'required'   => false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Address',
                ]
            ])

            ->add('github',TextType::class,[
                'constraints' => [new Length(['min' =>10,'max' =>100])],
                'required'   => false,
                'mapped'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Github Link',
                    'class'=>"form-control",
                ]
            ])
            ->add('website',TextType::class,[
                'constraints' => [new Length(['min' =>10,'max' =>100])],
                'required'   => false,
                'mapped'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Website Link',
                ]
            ])
            ->add('twitter',TextType::class,[
                'constraints' => [new Length(['min' =>10,'max' =>100])],
                'required'   => false,
                'mapped'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Twitter Link',
                ]
            ])
            ->add('instagram',TextType::class,[
                'constraints' => [new Length(['min' =>10,'max' =>100])],
                'required'   => false,
                'mapped'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Instagram Link',
                ]
            ])
            ->add('facebook',TextType::class,[
                'constraints' => [new Length(['min' =>10,'max' =>100])],
                'required'   => false,
                'mapped'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Facebook Link',
                ]
            ])
            ->add('linkedin',TextType::class,[
                'constraints' => [new Length(['min' =>10,'max' =>100])],
                'required'   => false,
                'mapped'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Linked In Link',
                ]
            ])
            ->add('Job',TextType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>100])],
                'required'   => false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Your Job',
                ]
            ])

            ->add('jobbingBei',TextType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>100])],
                'required'   => false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Working By',
                ]
            ])
            ->add('Bio',TextareaType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>1000])],
                'required'   => false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Write Your Bio',
                ]
            ])
            ->add('pwd',PasswordType::class,[
                'required'=> true,
                'mapped'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Confirm Your Password',
                    'style'=>'border: 2px solid #d33030;'
                ]
            ])
            ->add('Update',SubmitType::class,[
                'attr'=>[
                    'class'=>'w-100 btn btn-primary',
                    'placeholder'=>'Update',
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
