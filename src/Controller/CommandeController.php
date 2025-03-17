<?php

namespace App\Controller;

use App\Form\CommandeType;
use App\Entity\Commande;
use App\Entity\PrixVente;
use App\Repository\CommandeRepository;
use App\Repository\PrixVenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'commande.index')]
    public function index(Request $request, CommandeRepository $repository): Response
    {
        $commande = $repository->findAll(); 
        return $this->render('commande/index.html.twig', [
            'commandes' => $commande,
        ]);
    }

    #[Route('/commande/{id}/edit', 'commande.edit')]
    public function edit(Commande $commande, Request $request, EntityManagerInterface $em, PrixVenteRepository $prixVenteRepository)
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($commande);
            $em->flush();
            $this->addFlash("success", 'Le Commande a bien été modifié');
            return $this->redirectToRoute('commande.index');
        }
        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form
        ]);
    }

    #[Route('/commande/add', 'commande.add')]
    public function add(Request $request, EntityManagerInterface $em, SessionInterface $session): RedirectResponse|Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           
            $em->persist($commande);
            try {
                $em->flush();
                $this->addFlash('success', 'Le commande a été bien créé');
                return $this->redirectToRoute('commande.index');
            } catch (\Throwable $th) {
                $this->addFlash('danger', 'Commande déja existante.');
                return $this->redirectToRoute('commande.add', []);
            }
        }
        return $this->render('commande/add.html.twig', [
            'form' => $form,
        ]);
    }
}
