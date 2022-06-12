<?php

namespace App\Controller\Admin;

use App\Entity\Categorys;
use App\Entity\Products;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ){

    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $url = $this->adminUrlGenerator->setController(UserCrudController::class)->generateUrl();
        return $this->redirect($url);

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Unnammed')
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section("User's Fields");

        yield MenuItem::subMenu('Users','fas fa-user')->setSubItems([
            MenuItem::linkToCrud('Users', 'fas fa-eye', User::class)
        ]);

        yield MenuItem::subMenu('Products','fa-solid fa-list')->setSubItems([
            MenuItem::linkToCrud('Products', 'fas fa-plus', Products::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Products', 'fas fa-eye', Products::class)
        ]);

        yield MenuItem::subMenu('Categories','fa-solid fa-list')->setSubItems([
            MenuItem::linkToCrud('Categories', 'fas fa-plus', Categorys::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Categories', 'fas fa-eye', Categorys::class)
        ]);
        

        yield MenuItem::section("Admin's Fields");


        // yield MenuItem::subMenu('Accommodation','fa-solid fa-building')->setSubItems([
        //     MenuItem::linkToCrud('AccommodationType', 'fas fa-plus', AccomodationType::class)->setAction(Crud::PAGE_NEW),
        //     MenuItem::linkToCrud('AccommodationType', 'fas fa-eye', AccomodationType::class)
        // ]);

        // yield MenuItem::subMenu('Type Of Rent','fa-solid fa-clock-rotate-left')->setSubItems([
        //     MenuItem::linkToCrud('Type Of Rent', 'fas fa-plus', RentPeriodation::class)->setAction(Crud::PAGE_NEW),
        //     MenuItem::linkToCrud('Type Of Rent', 'fas fa-eye', RentPeriodation::class)
        // ]);
    }
}
