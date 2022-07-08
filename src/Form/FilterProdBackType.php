<?php

namespace App\Form;

use App\Entity\Categorys;
use App\Entity\SubCategories;
use App\MyClasses\FilterProdBack;
use App\Repository\CategorysRepository;
use App\Repository\SubCategoriesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterProdBackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('SearchBar',TextType::class,[
            'label' => 'Search',
            'required'=>false,
            'attr'=>[
                'placeholder'=>'ArticalNr, Name, Description, Category ...',
            ]
            ])

            ->add('ProdCat',EntityType::class,[
                'label' => 'Category' ,
                'class' => Categorys::class,
                'required'=>false,
                'query_builder'=>function(CategorysRepository $er){
                    return $query = $er->createQueryBuilder('c')
                        ->select('c')
                        ->andWhere('c.Status = true');
                    }
            ])

            ->add('ProdSubCat',EntityType::class,[
                'label' => 'SubCategory' ,
                'class' => SubCategories::class,
                'required'=>false,
                'query_builder'=>function(SubCategoriesRepository $er){
                    return $query = $er->createQueryBuilder('s')
                        ->select('s')
                        ->andWhere('s.Status = true');
                    }
            ])

            ->add('ProdPriceMax',MoneyType::class,[
                'label' => 'Max Price' ,
                'required' => false,
                'currency' => 'TND',              
            ])

            ->add('Filter',SubmitType::class,[
                'label' => 'Filter' ,
                'attr'=>[
                    'class'=>'btn p-2 fs-4 text-light w-100 btn-info mt-2',
                ]
            ])
            ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FilterProdBack::class,
            'methode'=>'GET',
        ]);
    }
}
