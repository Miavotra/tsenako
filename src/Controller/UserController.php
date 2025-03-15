<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


final class UserController extends AbstractController
{
    #[Route('/user', name: 'user.index')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/add', 'user.add')]
    public function addUser(Request $request, EntityManagerInterface $em,UserPasswordHasherInterface $passwordHasher): RedirectResponse|Response
    {
            $user = new User();
            $form = $this->createForm(UserType::class,$user);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                );
                $user->setPassword($hashedPassword);
                $em->persist($user);
                $em->flush();
                $this->addFlash('success','Le user a été bien créé');
                return $this->redirectToRoute('user.index');
            }
            return $this->render('user/add.html.twig',[
                'form' => $form
            ]);
    }
}
