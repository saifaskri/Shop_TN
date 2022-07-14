<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class CartController extends AbstractController
{
    public function __construct(
        private ProductsRepository $ProductsRepository,
    )
    {   
    }


    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_add_cart')]
    public function add($id): Response
    {
        $product = $this->ProductsRepository->find($id);
        return $this->render('cart/add.html.twig', [
            'product' =>$product,
        ]);
    }
}
