<?php

namespace App\Controller;
use DateTime;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    const ALLOWEDEXTENTION = ['JPG','JPEG','PNG'];
    const ENUM_GENDER = ['Male','Female','Others','Other'];


    
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, SluggerInterface $slugger, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
    
            $user->setCreatedAt(new \DateTime());
            $user->setActivation(0);
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

             // they All need To be checked
             $firstname =  $user->getFirstName();
             $lastname =  $user->getLastName();
             $email =  $user->getEmail();
             $password =  $user->getPassword();
             $gender =  $user->getGender();
             $Agree =  $form->get('agreeTerms')->getData();
             $fieldTesting=false;

             if(strlen($firstname)<3 || strlen($firstname)>15 ){
                $this->addFlash('Registry', 'FirstName Must be Between 3 and 15');
                $fieldTesting=true;
             }

             if(strlen($lastname)<3 || strlen($lastname)>15 ){
                $this->addFlash('Registry', 'LastName Must be Between 3 and 15');
                $fieldTesting=true;
             }

             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('Registry', 'Invalid Email');
                $fieldTesting=true;
             }

             if(strlen($password)<7 ){
                $this->addFlash('Registry', 'Password Must Be More Than 8 Caracters');
                $fieldTesting=true;
             }

             if(! in_array($gender,self::ENUM_GENDER) ){
                $this->addFlash('Registry', 'Invalid Gender Type');
                $fieldTesting=true;
             }

             if(! $Agree ){
                $this->addFlash('Registry', 'Must Agree Our Terms');
                $fieldTesting=true;
             }

            //if Errors Than Stop
             if($fieldTesting)return $this->render('registration/register.html.twig', ['registrationForm' => $form->createView(),]);
          
        ########################################################
        # Upload The Singel Image 
        ########################################################
        /** @var UploadedFile $brochureFile */
        $brochureFile = $form->get('ProfilePhoto')->getData();
        $insertInDBSingelImage = true ;
        $slugify = new Slugify();
        if ($brochureFile) {
        
            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename =$slugify->slugify($originalFilename);
            $newFilename = $safeFilename.'-'.md5(time().uniqid()).'.'.$brochureFile->guessExtension();
            $fileSize = $brochureFile->getSize();

            //check Extentions
            if(! in_array(strtoupper($brochureFile->guessExtension()),self::ALLOWEDEXTENTION) ){
                $this->addFlash('Registry', 'Bad Image Extenstion');
                $insertInDBSingelImage = false ;
            }

            // if File Large Than 10mb than throw error
            if($fileSize>10000000){
                $this->addFlash('Registry', 'Image Too Large Must be Under 10MB');
                $insertInDBSingelImage = false ;
            }

            // Move the file to the directory where brochures are stored
            try {
                if ($insertInDBSingelImage) {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $safeFilename
                    );
                    //done
                }
            }catch (FileException $e) {
                echo($e);
                die ;
            }
        }//end FILE Upload 
        ########################################################
        # END Upload The Singel Image 
        ########################################################

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
