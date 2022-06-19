<?php

namespace App\Controller;

use App\Entity\UserShop;
use App\Form\UserShopType;
use App\Repository\UserShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/shop')]
class UserShopController extends AbstractController
{
    #[Route('/', name: 'app_user_shop_index', methods: ['GET'])]
    public function index(UserShopRepository $userShopRepository): Response
    {
        return $this->render('user_shop/index.html.twig', [
            'user_shops' => $userShopRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_shop_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserShopRepository $userShopRepository): Response
    {
        $userShop = new UserShop();
        $form = $this->createForm(UserShopType::class, $userShop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userShopRepository->add($userShop, true);

            return $this->redirectToRoute('app_user_shop_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user_shop/new.html.twig', [
            'user_shop' => $userShop,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_shop_show', methods: ['GET'])]
    public function show(UserShop $userShop): Response
    {
        return $this->render('user_shop/show.html.twig', [
            'user_shop' => $userShop,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_shop_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserShop $userShop, UserShopRepository $userShopRepository): Response
    {
        $form = $this->createForm(UserShopType::class, $userShop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userShopRepository->add($userShop, true);

            return $this->redirectToRoute('app_user_shop_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user_shop/edit.html.twig', [
            'user_shop' => $userShop,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_shop_delete', methods: ['POST'])]
    public function delete(Request $request, UserShop $userShop, UserShopRepository $userShopRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userShop->getId(), $request->request->get('_token'))) {
            $userShopRepository->remove($userShop, true);
        }

        return $this->redirectToRoute('app_user_shop_index', [], Response::HTTP_SEE_OTHER);
    }
}
