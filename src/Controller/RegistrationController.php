<?php

namespace App\Controller;
use DateTime;
use App\Entity\User;
use App\Form\RegistrationFormType;
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

            //Upload User Profile Photo
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('ProfilePhoto')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.md5(time().uniqid()).'.'.$brochureFile->guessExtension();
               
                $fileSize = $brochureFile->getSize();
                // if File Large Than 5mb than throw error
                if($fileSize>5000000){
                 return $this->redirectToRoute('app_login');
                }

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                    $user->setUserProfilePhoto($newFilename);
                } catch (FileException $e) {
                    echo ($e);
                    die;
                }
            }

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
