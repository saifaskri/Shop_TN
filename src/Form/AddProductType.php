<?php

namespace App\Form;

use App\Entity\Categorys;
use App\Entity\Products;
use App\Repository\CategorysRepository;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\SlugType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;

class AddProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ProdName',TextType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>30])],
                'required'   => true,
            ])
            ->add('ProdIllustarion', FileType::class, [
                'label' => 'Profile Photo',
                'mapped' => false,
                'required' => false,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '10240K',
                        'maxSizeMessage' => 'Size Too Large Max 10MB',
                        // 'mimeTypes' => [
                        //     'image/jpeg',
                        //     'application/png',
                        //     'application/x-png',
                        // ],
                        // 'mimeTypesMessage' => 'Please upload An Image',
                    ])
                ],
            ])
            ->add('ProdPrice', MoneyType::class, [
                'currency' => 'TND',              
                'required'   => true,
            ])
            ->add('ProdDescription',TextareaType::class,[
                'constraints' => [new Length(['min' =>10,'max' =>1000])],
                'required'   => true,
            ])
            ->add('status',CheckboxType::class,[
                'label' => 'Visibility',
                'required'=>false
            ])
            ->add('category',EntityType::class,[
                'class' => Categorys::class,
                'required'=>true,
                'query_builder'=>function(CategorysRepository $er){
                    return $query = $er->createQueryBuilder('c')
                        ->select('c')
                        ->andWhere('c.Status = true');
                    }
            ])
            ->add('Add',SubmitType::class,[  
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ])

    ;

    }
}
