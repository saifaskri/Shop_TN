<?php

namespace App\Repository;

use App\Entity\Products;
use App\MyClasses\FilterProdBack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Products>
 *
 * @method Products|null find($id, $lockMode = null, $lockVersion = null)
 * @method Products|null findOneBy(array $criteria, array $orderBy = null)
 * @method Products[]    findAll()
 * @method Products[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    public function add(Products $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Products $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * @return Products[] Returns an array of Products objects
    */
   public function findByShop($value): array
   {
       return $this->createQueryBuilder('p')
           ->andWhere('p.BelongsToShop = :val')
           ->setParameter('val', $value)
           ->getQuery()
           ->getResult()
       ;
   }

    /**
     * @return Products[] Returns an array of Products objects
     */
    public function findByFiltersAdmin(FilterProdBack $filterProdBack,$UserShop): array
    {
        $query = $this
            ->createQueryBuilder('p')
            ->join('p.OwnedBy','OwnedBy')
            ->join('p.BelongsToShop','BelongsToShop')
            ->join('p.category','category')
            ->join('p.SubCategory','SubCategory')
            ->andWhere('BelongsToShop = :UserShop ')
            ->setParameter(':UserShop', $UserShop);
        ;

        if(!(empty($filterProdBack->SearchBar))){
            $query = $query
                ->andWhere('
                p.ProdName LIKE :search
                OR p.id = :id
                OR p.ProdDescription LIKE :search
                OR p.ProdSlug LIKE :search
                OR category.name  LIKE :search
                OR SubCategory.name  LIKE :search
                ')
                // OR OwnedBy.FirstName LIKE :search OR OwnedBy.LastName LIKE :search
                ->setParameter(':search', "%{$filterProdBack->SearchBar}%")
                ->setParameter(':id',$filterProdBack->SearchBar)
                
                ;

        }
        if(!(empty($filterProdBack->ProdPriceMax))){
            $query = $query
                ->andWhere('p.ProdPrice <= :Maxprice')
                ->setParameter(':Maxprice', $filterProdBack->ProdPriceMax);
        }
        if(!(empty($filterProdBack->ProdCat))){
            $query = $query
                ->andWhere('category.id IN (:Categorys)')
                ->setParameter(':Categorys', $filterProdBack->ProdCat);
        }
        if(!(empty($filterProdBack->ProdSubCat))){
            $query = $query
                ->andWhere('SubCategory.id IN (:SubCategory)')
                ->setParameter(':SubCategory', $filterProdBack->ProdSubCat);
        }
        return $query->getQuery()->getResult();
    }

    /**
     * @return Products[] Returns an array of Products objects
     */
    public function findByFiltersHome(FilterProdBack $filterProdBack = null): array
    {
        $query = $this
            ->createQueryBuilder('p')
            ->join('p.OwnedBy','OwnedBy')
            ->join('p.BelongsToShop','BelongsToShop')
            ->join('p.category','category')
            ->join('p.SubCategory','SubCategory')
            ->andWhere('p.status = true ')
            ->andWhere('BelongsToShop.status = true ')

        ;

        if(!(empty($filterProdBack->SearchBar))){
            $query = $query                
                ->andWhere('
                p.ProdName LIKE :search
                OR p.ProdDescription LIKE :search
                OR p.ProdSlug LIKE :search
                OR category.name  LIKE :search
                OR SubCategory.name  LIKE :search
                OR BelongsToShop.Shop_Name LIKE :search
                OR OwnedBy.FirstName LIKE :search OR OwnedBy.LastName LIKE :search
                ')
                ->setParameter(':search', "%{$filterProdBack->SearchBar}%");
        }
        if(!(empty($filterProdBack->ProdPriceMax))){
            $query = $query
                ->andWhere('p.ProdPrice <= :Maxprice')
                ->setParameter(':Maxprice', $filterProdBack->ProdPriceMax);
        }
        if(!(empty($filterProdBack->ProdCat))){
            $query = $query
                ->andWhere('category.id IN (:Categorys)')
                ->setParameter(':Categorys', $filterProdBack->ProdCat);
        }
        if(!(empty($filterProdBack->ProdSubCat))){
            $query = $query
                ->andWhere('SubCategory.id IN (:SubCategory)')
                ->setParameter(':SubCategory', $filterProdBack->ProdSubCat);
        }
        return $query->getQuery()->getResult();
    }


    
   public function FindBySlugAndId($slug,$id,$state=true): ?Products
   {
       return $this->createQueryBuilder('p')
            ->andWhere('
                p.id = :id
                AND 
                p.ProdSlug = :slug
                AND
                p.status = :state
            ')
            ->setParameter(':slug',$slug)
            ->setParameter(':id', $id)
            ->setParameter(':state',$state )
            ->getQuery()
            ->getOneOrNullResult()
       ;
   }



}
