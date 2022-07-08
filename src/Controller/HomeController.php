<?php

namespace App\Controller;

use App\Form\FilterProdBackType;
use App\MyClasses\FilterProdBack;
use App\Repository\ProductsRepository;
use App\Repository\SubCategoriesRepository;
use App\Repository\UserShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    public function __construct(
        private ProductsRepository $ProductsRepository,
        private SubCategoriesRepository $SubCategoriesRepository,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $userPasswordHasher,
        private Filesystem $filesystem,
        private UserShopRepository $UserShopRepository,
    )
    {   
        $this->SubCategoriese = $this->SubCategoriesRepository->findAll();
    }


    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $allProducts = $this->ProductsRepository->findByFiltersHome();

        //The Filter
        $productfilter = new FilterProdBack();
        $form = $this->createForm(FilterProdBackType::class, $productfilter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
             $productfilter->SearchBar = htmlentities($productfilter->SearchBar) ;
             $productfilter->ProdPriceMax = htmlentities($productfilter->ProdPriceMax) ;
             $allProducts = $this->ProductsRepository->findByFiltersHome($productfilter);
        }

        return $this->render('home/index.html.twig', [
            'AllProducts' => $allProducts,
            'FilterForm'=>$form->createView(),
            'saif'=>$this->SubCategoriese
        ]);
    }

    #[Route('/view/{slug}/{id}', name: 'app_view_product')]
    public function viewProduct($slug,$id): Response
    {

        $Product = $this->ProductsRepository->FindBySlugAndId($slug,$id);

        return $this->render('home/view.html.twig', [
            'Product' => $Product,
        ]);
    }

    #[Route('/shop-view/{ShopName}', name: 'view-shop-by-name')]
    public function ShopView($ShopName): Response
    {
        $shop = $this->UserShopRepository->findOneBy(['Shop_Name'=>$ShopName]);
        return $this->render('home/viewshop.html.twig',[
            'shop' =>$shop,
        ]); 

    }
    


    // #[Route('/', name: 'app_root')]
    // public function root(): Response
    // {    
    //     return $this->redirectToRoute('app_home'); 
    // }

}
