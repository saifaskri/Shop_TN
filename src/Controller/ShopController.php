<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository ;
use App\Entity\UserShop;
use App\Form\AddNewShopType;
use App\Form\DeleteShopUserType;
use App\Form\PasswordCheckerFormType;
use App\Repository\UserShopRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('user/shop')]
class ShopController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserShopRepository $userShopRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
         
    ){  
    }

    #[Route('/', name: 'app_index_shop')]
    public function index (): Response
    {
        if(!$this->getUser() || !$this->getUser()->getUserShop()) return $this->redirectToRoute('app_login');
        return $this->render('shop/index.html.twig', [
            'MyShop' =>$this->userShopRepository->find($this->getUser()->getUserShop()->getId()),
        ]);
    }


    #[Route('/add', name: 'app_add_shop')]
    public function new(Request $request, UserShopRepository $userShopRepository): Response
    {
        //if User Is Not lOGGED iN
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        //check if User Has a Shop Already
        if($this->getUser()->getUserShop()){
            return  $this->redirectToRoute('app_home');
        }
        $userShop = new UserShop();
        $form = $this->createForm(AddNewShopType::class, $userShop);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){

            // Check the Crsf Protection
            if (!$this->isCsrfTokenValid('AddShopName',$request->get('tokenShopAdd'))) {
                $this->addFlash('addShop', 'Can not Create because Of Secuirity Causes');
                return $this->render('shop/addShop.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $shopName = $userShop->getShopName();
            //check if name exisit
            if($shopName){
                //check for Numbers
                //check for Spacial Caracter
                if(preg_match('/[\'`^£$%&*()}{@#~?><>,|=_+¬-]/', $shopName)||preg_match('~[0-9]+~', $shopName)){
                    $this->addFlash('addShop', 'Shop Name Must contains Only Caracteres ');
                    return $this->render('shop/addShop.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
                //check if Name Exisit
                $deplicateName = $this->userShopRepository->findBy(array('Shop_Name' => $shopName));
                if(!empty($deplicateName)){
                    $this->addFlash('addShop', 'This Shop Name Is Already Exist ');
                    return $this->render('shop/addShop.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
                //check for Length 
                if(strlen($shopName)<3||strlen($shopName)>50){
                    $this->addFlash('addShop', 'Shop Name Must be More between 2-50 Caracters');
                    return $this->render('shop/addShop.html.twig', [
                        'form' => $form->createView(),
                    ]);
                } 
            }else{
                $this->addFlash('addShop', 'Please Choose A name For Your Shop');
                return $this->render('shop/addShop.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $userShop->setCreatedAt(new DateTime());
            $this->getUser()->setUserShop( $userShop);
            $userShop->setOwnedBy($this->getUser());

            $this->entityManager->persist($userShop);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_index_shop');  
        }
        return $this->render('shop/addShop.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/add/code', name: 'Bought_Code')]
    public function add_code (Request $request, UserRepository $UserRepository): Response
    {
        //if User Is Not lOGGED iN
        if(!$this->getUser()){return $this->redirectToRoute('app_login');}
        //check if User Has a Shop Already
        if($this->getUser()->getUserShop()){return  $this->redirectToRoute('app_home');}
        
        $form = $this->createForm(AddNewShopType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            // Check the Crsf Protection
            if (!$this->isCsrfTokenValid('AddShopName',$request->get('tokenShopAdd'))) {$this->addFlash('addcodeShop', 'Can not Create because Of Secuirity Causes');}
            $userShop = $this->userShopRepository->findOneByCode($form->get('Shop_Name')->getData());
            if($userShop){
                $oldUser = $UserRepository->findOneByUserShop($userShop->getId());
                $oldUser->setUserShop(null);
                $this->entityManager->flush();
                $this->getUser()->setUserShop($userShop);
                $userShop->setStatus($form->get('status')->getData());
                $userShop->setOwnedBy($this->getUser());
                $userShop->setSellingId(null);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_index_shop');  
            }else{
                $this->addFlash('addcodeShop', 'Wrong Code !');
            }
            //Search In DataBase

        }

        return $this->render('shop/addShopCode.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/Change-Activation', name: 'app_activation_shop')]
    public function Activation (Request $request, UserPasswordHasherInterface $userPasswordHasher ): Response
    {
        if(!$this->getUser() || !$this->getUser()->getUserShop()) return $this->redirectToRoute('app_login');
        $userShop =$this->userShopRepository->find($this->getUser()->getUserShop()->getId());
        $form = $this->createForm(PasswordCheckerFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            
            // Check Password
            $plainPwd = $form->get('plainPassword')->getData();
            if($userPasswordHasher->isPasswordValid($this->getUser(),$plainPwd)){
                $ShopStatus = $userShop->isStatus();
                $userShop->setStatus(!$ShopStatus);
                $this->entityManager->flush();
                //done
                return $this->redirectToRoute('app_index_shop');
            }else{
                $this->addFlash('BadPwd', 'Password Incorrect');
            }  
        }

        return $this->render('shop/Aktivation.html.twig', [
            'form'=>$form->createView(),
        ]);
    }


    #[Route('/delete', name: 'app_shop_delete')]
    public function delete(Request $request): Response
    {
        if(!$this->getUser() || !$this->getUser()->getUserShop()) return $this->redirectToRoute('app_login');
        if(count($request->request)===0)return $this->render('shop/DeleteShop.html.twig');

        $submittedToken = $request->request->get('tokenDeleteShop');
        if (! $this->isCsrfTokenValid('deleteShopName', $submittedToken)) {
            $this->addFlash('DeleteShop', 'Can not Delete Your Shop because Of Secuirity Causes');
            return $this->render('shop/DeleteShop.html.twig', []);
        }

        $plainPwd = $request->request->get('PlainPassword');
        //check Password
        if($this->userPasswordHasher->isPasswordValid($this->getUser(),$plainPwd)){
            //done
            $this->getUser()->setUserShop(null);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_login');  
        }else{
            $this->addFlash('DeleteShop', 'Password Incorrect');
            return $this->render('shop/DeleteShop.html.twig',[]);

        }  
        
        return $this->render('shop/DeleteShop.html.twig', [
        ]);
    }

    #[Route('/sell', name: 'app_sell_my_shop')]
    public function Sell(Request $request): Response
    {
        if(!$this->getUser() || !$this->getUser()->getUserShop()) return $this->redirectToRoute('app_login');

        //If Form Is Submitted
        if(count($request->request)!=0){
            $submittedToken = $request->request->get('tokenSellShop');
            if (! $this->isCsrfTokenValid('SellShopName', $submittedToken)) {
                $this->addFlash('SellShop', 'Can not Sell Your Shop because Of Secuirity Causes');
            }
            $plainPwd = $request->request->get('PlainPassword');
            //check Password
            if($this->userPasswordHasher->isPasswordValid($this->getUser(),$plainPwd)){
                $userShop =$this->userShopRepository->find($this->getUser()->getUserShop()->getId());
                // Toggel the Selling if cancel or not
                $selling_id = $userShop->getSellingId() ? null  : md5(uniqid().time()) ;
                $userShop->setSellingId($selling_id);
                $this->entityManager->flush();
                //done
                return $this->redirectToRoute('app_index_shop');  
            }else{
                $this->addFlash('SellShop', 'Password Incorrect');
            }  
        }

        return $this->render('shop/SellShop.html.twig',[
            'MyShop' =>$this->userShopRepository->find($this->getUser()->getUserShop()->getId()),
        ]); 
    }
    
    
}
