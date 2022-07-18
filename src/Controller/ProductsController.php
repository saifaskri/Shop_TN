<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\AddProductType;
use App\Form\FilterProdBackType;
use App\Form\ModProductType;
use App\MyClasses\FilterProdBack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductsRepository;
use App\Repository\SubCategoriesRepository;
use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class ProductsController extends AbstractController
{
    const ALLOWEDEXTENTION = ['JPG','JPEG','PNG'];
    const MAXUPLOAEDEFILECOUNT = 15 ;

    private $SubCategoriese;

    public function __construct(
        private ProductsRepository $ProductsRepository,
        private SubCategoriesRepository $SubCategoriesRepository,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $userPasswordHasher,
        private Filesystem $filesystem,
    )
    {   
        $this->SubCategoriese = $this->SubCategoriesRepository->findAll();
    }

    #[Route('/products', name: 'app_products')]
    public function index(Request $request): Response
    {

        //check if User Has a Shop 
        if(! $this->getUser()->getUserShop()){
            $this->addFlash('addShop', 'You Need To Create Shop First');
            return  $this->redirectToRoute('app_add_shop');
        }
        
        $allProducts = $this->ProductsRepository->findByShop($this->getUser()->getUserShop()->getId());

        //The Filter
        $productfilter = new FilterProdBack();
        $form = $this->createForm(FilterProdBackType::class, $productfilter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
             $productfilter->SearchBar = htmlentities($productfilter->SearchBar) ;
             $productfilter->ProdPriceMax = htmlentities($productfilter->ProdPriceMax) ;
             $allProducts = $this->ProductsRepository->findByFiltersAdmin($productfilter,$this->getUser()->getUserShop());
        }

        return $this->render('products/index.html.twig', [
            'AllProducts' => $allProducts,
            'FilterForm'=>$form->createView(),
            'saif'=>$this->SubCategoriese

        ]);
    }

    #[Route('/product/add', name: 'app_add_products')]
    public function add(Request $request, ): Response
    {
        //if User Is Not lOGGED iN
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        //check if User Has a Shop 
        if(! $this->getUser()->getUserShop()){
            $this->addFlash('addShop', 'You Need To Create Shop First');
            return  $this->redirectToRoute('app_add_shop');
        }


        $product = new Products();
        $form = $this->createForm(AddProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){

            $submittedToken = $request->request->get('tokenAddProduct');
            if (! $this->isCsrfTokenValid('AddProduct', $submittedToken)) {
                $this->addFlash('AddProduct', 'Can not Add because Of Secuirity Causes');
                return $this->render('products/add.html.twig', [
                    'form' => $form->createView() ,
                    'saif'=>$this->SubCategoriese
                ]);
            }
            // iNSERT The Product After Checking

            //check if Name Valid
            $nameProduct =  $product->getProdName();
            $nameProduct = htmlentities($nameProduct);
            if (strlen($nameProduct) < 3 || strlen($nameProduct) > 30  ){
                $this->addFlash('AddProduct', 'Name Must Be Between 3-30 Caracters');
                return $this->render('products/add.html.twig', [
                    'form' => $form->createView() ,
                    'saif'=>$this->SubCategoriese
                ]);            
            }

            //check if Descirption is Valid
            $DescriptionProd =  $product->getProdDescription();
            $DescriptionProd = htmlentities($DescriptionProd);
            if (strlen($DescriptionProd) < 10 || strlen(1000) > 30  ){
                $this->addFlash('AddProduct', 'Description Must Be Between 3-30 Caracters');
                return $this->render('products/add.html.twig', [
                    'form' => $form->createView() ,
                    'saif'=>$this->SubCategoriese
                ]);
            }

            //check if Category is Not Empty
            $CategoryProd =  $product->getCategory();
            $CategoryProd = htmlentities($CategoryProd);
            if (!$CategoryProd){
                $this->addFlash('AddProduct', 'Must Choose a Category');
                return $this->render('products/add.html.twig', [
                    'form' => $form->createView() ,
                    'saif'=>$this->SubCategoriese
                ]);
            }

            // //check if  SubCategory is Not Empty
            // $SubCategoryProd =  $product->getSubCategory();
            // $SubCategoryProd = htmlentities($SubCategoryProd);
            // if (!$SubCategoryProd){
            //     $this->addFlash('AddProduct', 'Must Choose a SubCategory');
            //     return $this->render('products/add.html.twig', ['form' => $form->createView()]);
            // }

            //check if  SubCategory Belongs To Category
            $SubCategoryProd =  $product->getSubCategory();
            $CategoryProd =  $product->getCategory();
            if ($SubCategoryProd && $SubCategoryProd->getMainCategory() !== $CategoryProd){
                $this->addFlash('AddProduct', 'SubCategory must correspond to MainCategory');
                return $this->render('products/add.html.twig', [
                    'form' => $form->createView() ,
                    'saif'=>$this->SubCategoriese
                ]);
            }

            $slugify = new Slugify();
            $Prodslug = $slugify->slugify($nameProduct);
            $product->setProdSlug($Prodslug);
            $product->setProdName($nameProduct);
            $product->setProdDescription($DescriptionProd);
            $product->setCreatedAt(new DateTime());
            $product->setOwnedBy($this->getUser());
            $product->setBelongsToShop($this->getUser()->getUserShop());

            #####################################################################################
            # UPLOAD MULTIPALE FILES
            #####################################################################################
                $illustartors = array();

                //Upload Product Image 
                /** @var UploadedFile $brochureFile */
                $brochureFiles = $form->get('ProdIllustarion')->getData();
                // this condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploaded
                $insertInDBMultipaleImages = true ;
                foreach ($brochureFiles as $brochureFile){
                    if ($brochureFile) {

                        $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                        // this is needed to safely include the file name as part of the URL
                        $safeFilename =$slugify->slugify($originalFilename);
                        $newFilename = $safeFilename.'-'.md5(time().uniqid()).'.'.$brochureFile->guessExtension();
                        $fileSize = $brochureFile->getSize();
        
                        //check Extentions
                        if(! in_array(strtoupper($brochureFile->guessExtension()),self::ALLOWEDEXTENTION) ){
                            $this->addFlash('AddProduct', 'Bad File Extenstion In Secondery Image');
                            $insertInDBMultipaleImages = false ;
                            break;
                        }

                        // if File Large Than 10mb than throw error
                        if($fileSize>10000000){
                            $this->addFlash('AddProduct', 'File Too Large Must be Under 10MB');
                            $insertInDBMultipaleImages = false ;
                            break;
                        }
        
                        // Move the file to the directory where brochures are stored
                        try {
                            if ($insertInDBMultipaleImages) {
                                $brochureFile->move($this->getParameter('brochures_directory')."/../ProdSingelImg",$newFilename);
                                array_push($illustartors,$newFilename);
                            }
                        } catch (FileException $e) {
                            echo($e);
                            die ;
                        }
                    }//end file Upload 
                }

                //check Max Uploaded File Count
                if(count($illustartors)>SELF::MAXUPLOAEDEFILECOUNT){
                    $insertInDBMultipaleImages = false ;
                    $this->addFlash('AddProduct', 'Max Uploaded File Count is '.SELF::MAXUPLOAEDEFILECOUNT);
                }

                //delete If File is Not like Required
                if (! $insertInDBMultipaleImages) {
                    foreach ($illustartors as $illustartor){
                        $filesystem = new Filesystem() ;
                        $filesystem->remove($this->getParameter('brochures_directory')."/../ProdSingelImg"."/".$illustartor);
                    }
                }
            #####################################################################################
            # END    Upload The MULTIPALE Image 
            #####################################################################################

            ########################################################
            # Upload The Singel Image 
            ########################################################
            $brochureFile = $form->get('ProdImgView')->getData();
            $insertInDBSingelImage = true ;
            if ($brochureFile) {
            
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename =$slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.md5(time().uniqid()).'.'.$brochureFile->guessExtension();
                $fileSize = $brochureFile->getSize();

                //check Extentions
                if(! in_array(strtoupper($brochureFile->guessExtension()),self::ALLOWEDEXTENTION) ){
                    $this->addFlash('AddProduct', 'Bad File Extenstion In Main Image');
                    $insertInDBSingelImage = false ;
                }

                // if File Large Than 10mb than throw error
                if($fileSize>10000000){
                    $this->addFlash('AddProduct', 'File Too Large Must be Under 10MB');
                    $insertInDBSingelImage = false ;
                }

                // Move the file to the directory where brochures are stored
                try {

                    if ($insertInDBSingelImage && $insertInDBMultipaleImages) {

                        $brochureFile->move($this->getParameter('brochures_directory')."/../ProdSingelImg",$newFilename);
                        $product->setProdImgView($newFilename);  
                        $product->setProdIllustarion(json_encode($illustartors));
                        $this->em->persist($product);
                        $this->em->flush();
                        $this->addFlash('addShopsucces', 'Product Added Successfully');

                        return $this->render('products/add.html.twig', [
                            'form' => $form->createView() ,
                            'saif'=>$this->SubCategoriese
                        ]);                        //done
                    }else if (!$insertInDBSingelImage && $insertInDBMultipaleImages){
                        // Delete Secondery Photos If Main is not Choosen
                        foreach ($illustartors as $illustartor){
                            $filesystem = new Filesystem() ;
                            $filesystem->remove($this->getParameter('brochures_directory')."/../ProdSingelImg"."/".$illustartor);
                        }
                    }

                }catch (FileException $e) {
                    echo($e);
                    die ;
                }

            }else if(!$brochureFile && !empty($illustartors)){
                
                // Delete Secondery Photos If Main is not Choosen
                    foreach ($illustartors as $illustartor){
                        $filesystem = new Filesystem() ;
                        $filesystem->remove($this->getParameter('brochures_directory')."/../ProdSingelImg"."/".$illustartor);
                    }
                $this->addFlash('AddProduct', 'Must Choose Main Image ');

            }else{
                $this->addFlash('AddProduct', 'Must Choose Main Image ');
            }//end FILE Upload 

            ########################################################
            # END    Upload The Singel Image 
            ########################################################

        }//end form is submmited
        

        return $this->render('products/add.html.twig', [
            'form' => $form->createView() ,
            'saif'=>$this->SubCategoriese
        ]);
    }

    #[Route('/product/modify/{slug}/{id}', name: 'app_mod_products')]
    public function Modify($slug,Products $product , Request $request ): Response
    {
        //if User Is Not lOGGED iN
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        //check if User Has a Shop 
        if(! $this->getUser()->getUserShop()){
            $this->addFlash('addShop', 'You Need To Create Shop First');
            return  $this->redirectToRoute('app_add_shop');
        }

        $form = $this->createForm(ModProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $submittedToken = $request->request->get('tokenModProduct');
            if (! $this->isCsrfTokenValid('ModProduct', $submittedToken)) {
                $this->addFlash('ModProduct', 'Can not Modify because Of Secuirity Causes');
                return $this->render('products/modify.html.twig', [
                    'form' => $form->createView() ,
                    'saif'=>$this->SubCategoriese
                ]);
            }
            //Start Modifing
            // iNSERT The Product After Checking

           //check if Name Valid
           $nameProduct =  $product->getProdName();
           $nameProduct = htmlentities($nameProduct);
           if (strlen($nameProduct) < 3 || strlen($nameProduct) > 30  ){
               $this->addFlash('ModProduct', 'Name Must Be Between 3-30 Caracters');
               return $this->render('products/modify.html.twig', [
                   'form' => $form->createView() ,
                   'saif'=>$this->SubCategoriese
               ]);            
           }

           //check if Descirption is Valid
           $DescriptionProd =  $product->getProdDescription();
           $DescriptionProd = htmlentities($DescriptionProd);
           if (strlen($DescriptionProd) < 10 || strlen(1000) > 30  ){
               $this->addFlash('ModProduct', 'Description Must Be Between 3-30 Caracters');
               return $this->render('products/modify.html.twig', [
                   'form' => $form->createView() ,
                   'saif'=>$this->SubCategoriese
               ]);
           }

           //check if Category is Not Empty
           $CategoryProd =  $product->getCategory();
           $CategoryProd = htmlentities($CategoryProd);
           if (!$CategoryProd){
               $this->addFlash('ModProduct', 'Must Choose a Category');
               return $this->render('products/modify.html.twig', [
                   'form' => $form->createView() ,
                   'saif'=>$this->SubCategoriese
               ]);
           }

           // //check if  SubCategory is Not Empty
           // $SubCategoryProd =  $product->getSubCategory();
           // $SubCategoryProd = htmlentities($SubCategoryProd);
           // if (!$SubCategoryProd){
           //     $this->addFlash('AddProduct', 'Must Choose a SubCategory');
           //     return $this->render('products/add.html.twig', ['form' => $form->createView()]);
           // }

           //check if  SubCategory Belongs To Category
           $SubCategoryProd =  $product->getSubCategory();
           $CategoryProd =  $product->getCategory();
           if ($SubCategoryProd && $SubCategoryProd->getMainCategory() !== $CategoryProd){
               $this->addFlash('ModProduct', 'SubCategory must correspond to MainCategory');
               return $this->render('products/modify.html.twig', [
                   'form' => $form->createView() ,
                   'saif'=>$this->SubCategoriese
               ]);
           }

           $slugify = new Slugify();
           $Prodslug = $slugify->slugify($nameProduct);
           $product->setProdSlug($Prodslug);
           $product->setProdName($nameProduct);
           $product->setProdDescription($DescriptionProd);
           $product->setCreatedAt(new DateTime());
           $product->setOwnedBy($this->getUser());
           $product->setBelongsToShop($this->getUser()->getUserShop());

            #####################################################################################
            # UPLOAD MULTIPALE FILES
            #####################################################################################
            $illustartorsMultipale = array();

            //Upload Product Image 
            /** @var UploadedFile $brochureFile */
            $brochureFiles = $form->get('ProdIllustarion')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            foreach ($brochureFiles as $brochureFile){
                $insertInDBMultipaleImages = false ;
                if ($brochureFile) {
                    $insertInDBMultipaleImages = true ;

                    $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename =$slugify->slugify($originalFilename);
                    $newFilename = $safeFilename.'-'.md5(time().uniqid()).'.'.$brochureFile->guessExtension();
                    $fileSize = $brochureFile->getSize();
    
                    //check Extentions
                    if(! in_array(strtoupper($brochureFile->guessExtension()),self::ALLOWEDEXTENTION) ){
                        $this->addFlash('ModProduct', 'Bad File Extenstion In Secondery Image');
                        $insertInDBMultipaleImages = false ;
                        break;
                    }

                    // if File Large Than 10mb than throw error
                    if($fileSize>10000000){
                        $this->addFlash('ModProduct', 'File Too Large Must be Under 10MB');
                        $insertInDBMultipaleImages = false ;
                        break;
                    }
    
                    // Move the file to the directory where brochures are stored
                    try {
                        if ($insertInDBMultipaleImages) {
                            $brochureFile->move($this->getParameter('brochures_directory')."/../ProdSingelImg",$newFilename);
                            array_push($illustartorsMultipale,$newFilename);
                        }
                    } catch (FileException $e) {
                        echo($e);
                        die ;
                    }
                }//end file Upload 
            }

            //check Max Uploaded File Count
            if(count($illustartorsMultipale)>SELF::MAXUPLOAEDEFILECOUNT){
                $insertInDBMultipaleImages = false ;
                $this->addFlash('ModProduct', 'Max Uploaded File Count is '.SELF::MAXUPLOAEDEFILECOUNT);
            }

            //delete If File is Not like Required
            if (! $insertInDBMultipaleImages) {
                foreach ($illustartorsMultipale as $illustartor){
                    $filesystem = new Filesystem() ;
                    $filesystem->remove($this->getParameter('brochures_directory')."/../ProdSingelImg"."/".$illustartor);
                }
                $illustartorsMultipale = array();
            }

            if(count($illustartorsMultipale)>0){
                $product->setProdIllustarion(json_encode($illustartorsMultipale));
            }

        #####################################################################################
        # END  Upload The MULTIPALE Image 
        #####################################################################################

        ########################################################
        # Upload The Singel Image 
        ########################################################
        $brochureFile = $form->get('ProdImgView')->getData();
        $insertInDBSingelImage = false ;
        if ($brochureFile) {
            $insertInDBSingelImage = true ;

            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename =$slugify->slugify($originalFilename);
            $newFilename = $safeFilename.'-'.md5(time().uniqid()).'.'.$brochureFile->guessExtension();
            $fileSize = $brochureFile->getSize();

            //check Extentions
            if(! in_array(strtoupper($brochureFile->guessExtension()),self::ALLOWEDEXTENTION) ){
                $this->addFlash('ModProduct', 'Bad File Extenstion In Main Image');
                $insertInDBSingelImage = false ;
            }

            // if File Large Than 10mb than throw error
            if($fileSize>10000000){
                $this->addFlash('ModProduct', 'File Too Large Must be Under 10MB');
                $insertInDBSingelImage = false ;
            }

            // Move the file to the directory where brochures are stored
            try {

                if ($insertInDBSingelImage && $insertInDBMultipaleImages) {

                    $brochureFile->move($this->getParameter('brochures_directory')."/../ProdSingelImg",$newFilename);
                    //Delete Old Foto
                    $oldPhoto = $product->getProdImgView();
                    $this->filesystem->remove($this->getParameter('brochures_directory')."/../ProdSingelImg"."/".$oldPhoto);
                    $product->setProdImgView($newFilename);
                   

                    // return $this->render('products/modify.html.twig', [
                    //     'form' => $form->createView() ,
                    //     'saif'=>$this->SubCategoriese
                    // ]);  

                    //done
                }else if (!$insertInDBSingelImage && $insertInDBMultipaleImages){
                    // Delete Secondery Photos If Main is not Choosen
                    foreach ($illustartors as $illustartor){
                        $filesystem = new Filesystem() ;
                        $filesystem->remove($this->getParameter('brochures_directory')."/../ProdSingelImg"."/".$illustartor);
                    }
                }
            }catch (FileException $e) {
                echo($e);
                die ;
            }
        }//end FILE Upload 
        ########################################################
        # END Upload The Singel Image 
        ########################################################
            if ($insertInDBSingelImage && $insertInDBMultipaleImages){
                    $this->em->flush();
                    $this->addFlash('ModShopsucces', 'Product '.$slug.' Modified Successfully');
                    // $this->filesystem->remove($this->getParameter('brochures_directory')."/../ProdSingelImg"."/".$oldPhoto);
                return  $this->redirectToRoute('app_products');
            }
            
        }//end Form

        return $this->render('products/modify.html.twig', [
            'form' => $form->createView() ,
            'saif'=>$this->SubCategoriese
        ]);
    }

    #[Route('/product/delete/{slug}/{id}', name: 'app_del_products')]
    public function Delete($slug,Products $product , Request $request ): Response
    {
        //if User Is Not lOGGED iN
        if(!$this->getUser()){return $this->redirectToRoute('app_login');}

        //check if User Has a Shop 
        if(! $this->getUser()->getUserShop()){
            $this->addFlash('addShop', 'You Need To Create Shop First');
            return  $this->redirectToRoute('app_add_shop');
        }

        //if Form is Submmited
        if (count($request->request)>0) {
            //check the Crsf Protection   
            $submittedToken = $request->request->get('tokenDeleteProd');
            if (! $this->isCsrfTokenValid('deleteProd', $submittedToken)) {
                $this->addFlash('DelProduct', 'Can not Modify because Of Secuirity Causes');
                return $this->render('products/delete.html.twig', ['Product'=>$product ]);
            }

            $plainPwd = $request->request->get('PlainPassword');
            //check Password
            if($this->userPasswordHasher->isPasswordValid($this->getUser(),$plainPwd)){

                ////Start Deleting
                $this->ProductsRepository->remove($product,true);
                $this->addFlash('deleteProdSucces', 'Product '.$slug. ' Deleted Successfully');
                return  $this->redirectToRoute('app_products');
                //done

            }else{$this->addFlash('DelProduct', 'Password Incorrect');} 

        }
         

        return $this->render('products/delete.html.twig', ['Product'=>$product ]);
    }


}
