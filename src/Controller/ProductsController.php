<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\AddProductType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductsRepository;
use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/user')]
class ProductsController extends AbstractController
{

    public function __construct(
        private ProductsRepository $ProductsRepository,
        private EntityManagerInterface $em
    )
    {   
    }

    #[Route('/products', name: 'app_products')]
    public function index(): Response
    {
        $allProducts = $this->ProductsRepository->findByShop($this->getUser()->getUserShop()->getId());
        return $this->render('products/index.html.twig', [
            'AllProducts' => $allProducts,
        ]);
    }

    #[Route('/product/add', name: 'app_add_products')]
    public function add(Request $request): Response
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
                $this->addFlash('AddProduct', 'Can not Delete Your Shop because Of Secuirity Causes');
                return $this->render('products/add.html.twig', ['form' => $form->createView()]);
            }
            // iNSERT The Product After Checking

            //check if Name Valid
            $nameProduct =  $product->getProdName();
            $nameProduct = htmlentities($nameProduct);
            if (strlen($nameProduct) < 3 || strlen($nameProduct) > 30  ){
                $this->addFlash('AddProduct', 'Name Must Be Between 3-30 Caracters');
            }

            //check if Descirption is Valid
            $DescriptionProd =  $product->getProdDescription();
            $DescriptionProd = htmlentities($DescriptionProd);
            if (strlen($DescriptionProd) < 10 || strlen(1000) > 30  ){
                $this->addFlash('AddProduct', 'Description Must Be Between 3-30 Caracters');
            }

            $slugify = new Slugify();
            $Prodslug = $slugify->slugify($nameProduct);
            $product->setProdSlug($Prodslug);
            $product->setProdName($nameProduct);
            $product->setProdDescription($DescriptionProd);
            $product->setCreatedAt(new DateTime());
            $product->setOwnedBy($this->getUser());
            $product->setBelongsToShop($this->getUser()->getUserShop());

             //Upload Product Image P
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('ProdIllustarion')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename =$slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.md5(time().uniqid()).'.'.$brochureFile->guessExtension();
               
                $fileSize = $brochureFile->getSize();

                // if File Large Than 10mb than throw error
                if($fileSize>10000000){
                    $this->addFlash('AddProduct', 'File Too Large Must be Under 10MB');
                    return $this->render('products/add.html.twig', [
                        'form' => $form->createView() ,
                    ]);
                }

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory')."/../ProdSingelImg",
                        $newFilename

                    );
                    $product->setProdIllustarion($newFilename);
                } catch (FileException $e) {
                    echo($e);
                    die;
                }
            }//end fole Upload 

            $this->em->persist($product);
            $this->em->flush();
            $this->addFlash('addShopsucces', 'Product Added Successfully');

        }
        
        return $this->render('products/add.html.twig', [
            'form' => $form->createView() ,
        ]);
    }
    
    #[Route('/product/modify/{slug}/{id}', name: 'app_products')]
    public function index($slug,$id): Response
    {
        return $this->render('products/modify.html.twig', [
            'AllProducts' => $allProducts,
        ]);
    }




}
