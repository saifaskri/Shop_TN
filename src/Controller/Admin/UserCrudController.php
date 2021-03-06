<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCrudController extends AbstractCrudController
{
    public const ADVERTISMENT_BASE_PATH='uploads/ProfilePhoto';
    public const ADVERTISMENT_UPLOAD_FOTOS='public/uploads/ProfilePhoto';

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN')
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setDisabled()
                ->hideOnForm(),
            EmailField::new('email')
                ->setDisabled(),
            TextField::new('firstname')
                ->hideOnForm()
                ->setDisabled(),
            TextField::new('lastname')
                ->hideOnForm()
                ->setDisabled(),
            TextField::new('Gender')
                ->hideOnForm()
                ->setDisabled(),
            DateField::new('BirthDay')
                ->hideOnForm()
                ->setDisabled()
                ->setRequired(true),
            ArrayField::new('roles'),
            ImageField::new('user_profile_photo','Profile Photo')
                ->hideOnForm()
                ->setDisabled()
                ->setRequired(false)
                ->setBasePath(self::ADVERTISMENT_BASE_PATH)
                ->setUploadDir(self::ADVERTISMENT_UPLOAD_FOTOS),
            TelephoneField::new('Tel','Phone Number')
                ->setDisabled(),
            DateTimeField::new('createdat','Created At')
                ->hideOnForm(),
            DateTimeField::new('updatedat','Last Modification')
                ->hideOnForm(),
            BooleanField::new('activation','Activated'),

        ];
    }

     public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
     {
         if (($entityInstance instanceof User)) return;
     }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) return ;
        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User)
        parent::updateEntity($entityManager, $entityInstance);
    }


}
