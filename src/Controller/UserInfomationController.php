<?php

namespace App\Controller;

use App\Form\UserSettingsType;
use App\Repository\UserRepository;
use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserInfomationController extends AbstractController
{
    const ALLOWEDEXTENTION = ['JPG','JPEG','PNG'];
    const ENUM_GENDER = ['Male','Fmale','Others'];


    public function __construct(
        private UserRepository $UserRepository,
        private UserSettingsType $UserSettingsType,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager
    )
    { 
    }

    #[Route('/view/{slug}/{id}', name: 'app_user_infomation_index')]
    public function index($id): Response
    {
        $userInfo = $this->UserRepository->find($id);
        if (! $userInfo) return $this->redirectToRoute('app_home');

        return $this->render('user_infomation/index.html.twig', [
            'userInfo'=>$userInfo,
        ]);
    }


    #[Route('/modify-my-info', name: 'app_user_modify_userinfo')]
    public function Modify(Request $request): Response
    {
        //check if User Logged In
        if(! $this->getUser()) $this->redirectToRoute('app_home');

        $mySocialLinks = $this->UserRepository->find($this->getUser()->getId())->getSocialLinks()[0];
        $form = $this->createForm(UserSettingsType::class,$this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $submittedToken = $request->request->get('tokenModUser');
            if (! $this->isCsrfTokenValid('ModUser', $submittedToken)) {
                $this->addFlash('ModifyAccountUser', 'Can not Modify because Of Secuirity Causes');
                return $this->render('user_infomation/modifyInfo.html.twig', [
                    'form'=> $form->createView(),
                    'socialLinks'=>$mySocialLinks,
                ]);
        
            }
            

            // Check Password
            $plainPwd = $form->get('pwd')->getData();
            if($this->userPasswordHasher->isPasswordValid($this->getUser(),$plainPwd)){
                //do it

                // they All need To be checked
                $firstname =  $this->getUser()->getFirstName();
                $lastname =  $this->getUser()->getLastName();
                $email =  $this->getUser()->getEmail();
                $password =  $this->getUser()->getPassword();
                $gender =  $this->getUser()->getGender();

                $fieldTesting=false;

                if(strlen($firstname)<3 || strlen($firstname)>15 ){
                    $this->addFlash('ModifyAccountUser', 'FirstName Must be Between 3 and 15');
                    $fieldTesting=true;
                }

                if(strlen($lastname)<3 || strlen($lastname)>15 ){
                    $this->addFlash('ModifyAccountUser', 'LastName Must be Between 3 and 15');
                    $fieldTesting=true;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->addFlash('ModifyAccountUser', 'Invalid Email');
                    $fieldTesting=true;
                }

                if(strlen($password)<7 ){
                    $this->addFlash('ModifyAccountUser', 'Password Must Be More Than 8 Caracters');
                    $fieldTesting=true;
                }

                if(! in_array($gender,self::ENUM_GENDER) ){
                    $this->addFlash('ModifyAccountUser', 'Invalid Gender Type');
                    $fieldTesting=true;
                }

                //if Errors Than Stop
                if($fieldTesting)return $this->render('user_infomation/modifyInfo.html.twig', [
                    'form'=> $form->createView(),
                    'socialLinks'=>$mySocialLinks,
                ]);
            
                $this->getUser()->setUpdatedAt(new \DateTime());

                //check if Tel is Correct
                $TelNumber = $this->getUser()->getTel();
                if(preg_match('#[^0-9]#',$TelNumber)){
                    $this->addFlash('ModifyAccountUser', 'Wrong Phone Number Type');
                    return $this->render('user_infomation/modifyInfo.html.twig', [
                        'form'=> $form->createView(),
                        'socialLinks'=>$mySocialLinks,
                    ]);
                }

                $socialLinks = array();
                $github = $form->get('github')->getData();
                $website = $form->get('website')->getData();
                $twitter = $form->get('twitter')->getData();
                $instagram = $form->get('instagram')->getData();
                $facebook = $form->get('facebook')->getData();
                $linkedin = $form->get('linkedin')->getData();
                array_push($socialLinks,[
                    'Github'=>$github,
                    'Website'=>$website,
                    'twitter'=>$twitter,
                    'Instagram'=>$instagram,
                    'Facebook'=>$facebook,
                    'Linkedin'=>$linkedin,
                ]);
                //check if Urls are Correct
                $socialLinksValidator = true ;
                $ErrorMsg = 'Bad Links For Your => ';
                foreach ($socialLinks[0] as $SiteName => $SiteLink) {
                    if( (! (filter_var($SiteLink, FILTER_VALIDATE_URL))) && strlen($SiteLink)!==0 ){
                        $ErrorMsg .=$SiteName.' ';
                        $socialLinksValidator = false;
                    }
                }
                if(! $socialLinksValidator) {
                    $this->addFlash('ModifyAccountUser', $ErrorMsg );
                    return $this->render('user_infomation/modifyInfo.html.twig', ['form'=> $form->createView(),'socialLinks'=>$mySocialLinks,]);
                }else{
                    $this->getUser()->setSocialLinks($socialLinks);
                }

                ########################################################
                # Upload The Singel Image 
                ########################################################
                /** @var UploadedFile $brochureFile */
                $brochureFile = $form->get('ProfilePhoto')->getData();
                $slugify = new Slugify();
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
                        $this->addFlash('ModifyAccountUser', 'Bad Image Extenstion');
                        $insertInDBSingelImage = false ;
                    }

                    // if File Large Than 10mb than throw error
                    if($fileSize>10000000){
                        $this->addFlash('ModifyAccountUser', 'Image Too Large Must be Under 10MB');
                        $insertInDBSingelImage = false ;
                    }

                    // Move the file to the directory where brochures are stored
                    try {
                        if ($insertInDBSingelImage) {
                            $brochureFile->move(
                                $this->getParameter('brochures_directory'),
                                $newFilename
                            );
                            $filesystem = new Filesystem() ;
                            $oldImage = $this->getUser()->getUserProfilePhoto();
                            $filesystem->remove($this->getParameter('brochures_directory').'/'.$oldImage);        
                            $this->getUser()->setUserProfilePhoto($newFilename);
                            //done
                        }else{ 
                            return $this->render('user_infomation/modifyInfo.html.twig', [
                                'form'=> $form->createView(),
                                'socialLinks'=>$mySocialLinks,
                            ]);
                        }
                    }catch (FileException $e) {
                        echo($e);
                        die ;
                    }
                }//end FILE Upload 
                ########################################################
                # END Upload The Singel Image 
                ########################################################
                $this->entityManager->flush();
                $this->addFlash('ModifyAccountUserSucces', 'Your Infos Are Updated Successfully');

            }else{
                $this->addFlash('ModifyAccountUser', 'Password Incorrect');
            }  

        }

        return $this->render('user_infomation/modifyInfo.html.twig', [
            'form'=> $form->createView(),
            'socialLinks'=>$mySocialLinks,
        ]);
    }

    #[Route('/change-my-password', name: 'app_user_infomation_change_password')]
    public function ChangePassword(Request $request): Response
    {
        $userInfo = $this->getUser();
        if (! $userInfo) return $this->redirectToRoute('app_home');

        if(isset($request->query->all()['CodeToken'])){
            $EmailCode = $request->query->all()['CodeToken'];
        }

        return $this->render('user_infomation/changePassword.html.twig',[
            'userInfo'=>$userInfo,
        ]);
    }

} 
