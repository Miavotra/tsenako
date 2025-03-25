<?php

namespace App\Controller;

use App\Form\PrixVenteType;
use App\Entity\PrixVente;
use App\Repository\PrixVenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PrixVenteController extends AbstractController
{ 
    #[Route('/prix/vente', name: 'produit.prix.vente.index')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, PrixVenteRepository $repository): Response
    {
        $prix_vente = $repository->findAll();
        //dd($produit);
        return $this->render('prix_vente/index.html.twig', [
            'prix_vente' => $prix_vente,
        ]);
    }

    #[Route('/prix/vente/add', 'produit.prix.vente.add')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, EntityManagerInterface $em, SessionInterface $session): RedirectResponse|Response
    {
        $prix_vente = new PrixVente();
        $form = $this->createForm(PrixVenteType::class,$prix_vente);
        $form->handleRequest($request);
        $session->all(); 
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($prix_vente);
            $em->flush();
            $this->addFlash('success','Le prix de vente a été bien créé');
            return $this->redirectToRoute('produit.prix.vente.index');
        }
        return $this->render('prix_vente/add.html.twig',[
            'form' => $form
        ]);
    }
}
