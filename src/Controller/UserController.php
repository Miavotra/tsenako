<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class UserController extends AbstractController
{
    #[Route('/user', name: 'user.index')]
    public function index(Request $request, UserRepository $repository): Response
    {
        $user = $repository->findAll(); 
        return $this->render('user/index.html.twig', [
            'users' => $user,
        ]);
    }

    #[Route('/user/add', 'user.add')]
    #[IsGranted('ROLE_USER')]
    public function addUser(Request $request, EntityManagerInterface $em,UserPasswordHasherInterface $passwordHasher): Response
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
