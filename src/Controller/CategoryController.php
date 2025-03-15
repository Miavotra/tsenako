<?php

namespace App\Controller;

use App\Form\CategoryType;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{ 
    #[Route('/category', name: 'produit.category.index')]
    public function index(Request $request, CategoryRepository $repository): Response
    {
        $category = $repository->findAll();
        return $this->render('category/index.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/category/add', 'produit.category.add')]
    public function add(Request $request, EntityManagerInterface $em, SessionInterface $session): RedirectResponse|Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        dump($session->all());
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($category);
            $em->flush();
            $this->addFlash('success','La categorie a été bien créé');
            return $this->redirectToRoute('produit.category.index');
        }
        return $this->render('category/add.html.twig',[
            'form' => $form
        ]);
    }
}
