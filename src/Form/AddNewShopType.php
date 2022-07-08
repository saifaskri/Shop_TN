<?php

namespace App\Form;
use App\Entity\UserShop;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;

class AddNewShopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Shop_Name',TextType::class,[
                'label'=>'Shop Name',
                'constraints' => [new Length(['min' =>2,'max' =>50])],
                'required'=>true
            ])
            ->add('status',CheckboxType::class, [
                'label'=>'Visibile',
                'required'=>false
            ])
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
            'data_class' => UserShop::class,
            // enable/disable CSRF protection for this form
            'csrf_protection' => false,
            'method'=>"POST",
            
        ]);
    }
}
