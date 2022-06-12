<?php

namespace App\Controller\Admin;

use App\Entity\Products;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
class ProductsCrudController extends AbstractCrudController
{
    public const ADVERTISMENT_BASE_PATH='uploads/ProdSingelImg';
    public const ADVERTISMENT_UPLOAD_FOTOS='public/uploads/ProdSingelImg';
   

    public static function getEntityFqcn(): string
    {
        return Products::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id','Ads_Id')->hideOnForm(),
            AssociationField::new('OwnedBy','Posted By')
            ->hideOnForm(),
            TextField::new('ProdName')
            ->setRequired(true),
            SlugField::new('ProdSlug')
            ->hideOnDetail()
            ->hideOnIndex()
            ->setTargetFieldName('ProdName')
            ->setRequired(true),
            AssociationField::new('category','Category')
                ->setRequired(true)
                ->setQueryBuilder(function(ORMQueryBuilder $queryBuilder){
                    $queryBuilder->where('entity.Status = true');
                }),
            DateTimeField::new('createdat')
                ->hideOnForm(),
            DateTimeField::new('updatedat')
                ->hideOnForm(),
            MoneyField::new('ProdPrice')->setCurrency('TND')
                ->setRequired(true),
            ImageField::new('ProdIllustarion')
                ->setRequired(false)
                ->hideWhenUpdating()
                ->setBasePath(self::ADVERTISMENT_BASE_PATH)
                ->setUploadDir(self::ADVERTISMENT_UPLOAD_FOTOS)
                ->setUploadedFileNamePattern(md5(time().uniqid()).'[randomhash][name].[extension]'),
            BooleanField::new('status','Visibility'),
            TextEditorField::new('ProdDescription')
                ->setRequired(true),
        ];
    }
    
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (($entityInstance instanceof Products )){
            if($this->getUser()){
                $entityInstance->setOwnedBy($this->getUser());
                $entityInstance->setCreatedAt(new DateTime() );
            } 
            if( !$entityInstance->getProdIllustarion()){
                $entityInstance->setProdIllustarion('NoPrudctImg.jpg');
            }
        }
        parent::persistEntity($entityManager, $entityInstance);
    }


}
