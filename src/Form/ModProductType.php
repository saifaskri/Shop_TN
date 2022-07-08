<?php

namespace App\Form;

use App\Entity\Categorys;
use App\Entity\Products;
use App\Entity\SubCategories;
use App\Repository\CategorysRepository;
use App\Repository\SubCategoriesRepository;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
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
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ModProductType extends AbstractType
{
    public $saif;
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ProdName',TextType::class,[
                'constraints' => [new Length(['min' =>3,'max' =>30])],
                'required'   => true,
            ])
            ->add('ProdIllustarion', FileType::class, [
                'label' => 'Secondry Image',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new All([
                      'constraints' => [
                        new File([
                          'maxSize' => '10240k',
                          'maxSizeMessage' => 'Size Too Large Max 10MB',
                          'mimeTypesMessage' => 'Please upload An Image',
                            // 'mimeTypes' => [
                            //      'image/jpeg',
                            //      'application/png',
                            //      'application/x-png',
                            // ],
                            // 'mimeTypesMessage' => 'Please upload An Image',         
                        ]),
                      ],
                    ]),
                  ]
            ])

            ->add('ProdImgView', FileType::class, [
                'label' => 'Main Image',
                'mapped' => false,
                'required' => false,
                'multiple' => false,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes                    
                      'constraints' => [
                        new File([
                          'maxSize' => '10240k',
                          'maxSizeMessage' => 'Size Too Large Max 10MB',
                          'mimeTypesMessage' => 'Please upload An Image',
                            // 'mimeTypes' => [
                            //      'image/jpeg',
                            //      'application/png',
                            //      'application/x-png',
                            // ],
                            // 'mimeTypesMessage' => 'Please upload An Image',         
                        ]),
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
                'label'=>'Category',
                'class' => Categorys::class,
                'required'=>true,
                'query_builder'=>function(CategorysRepository $er){
                    return $query = $er->createQueryBuilder('c')
                        ->select('c')
                        ->andWhere('c.Status = true');
                    }
            ])
            ->add('SubCategory',EntityType::class,[
                'label'=>'SubCategory',
                'class' => SubCategories::class,
                'required'=>false,
                'query_builder'=>function(SubCategoriesRepository $er){
                    return $query = $er->createQueryBuilder('s')
                        ->select('s')
                        ->join('s.MainCategory','MainCategory')
                        ->andWhere('s.Status = true ')
                        ;
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
