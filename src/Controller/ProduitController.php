<?php

namespace App\Controller;

use App\Form\ProduitType;
use App\Entity\Produit;
use App\Entity\PrixVente;
use App\Repository\ProduitRepository;
use App\Repository\PrixVenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

final class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'produit.index')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, ProduitRepository $repository): Response
    {
        $produit = $repository->findAll(); 
        return $this->render('produit/index.html.twig', [
            'produits' => $produit,
        ]);
    }

    #[Route('/api/produits', name: 'api_list_produits', methods: ['GET'])]
    public function apiProduitList(Request $request, ProduitRepository $repository): JsonResponse
    {
        $produits = $repository->findAll(); 

        $res = [];
        foreach ($produits as $key => $produit) {
            $obj = new stdClass();
            $obj->id = $produit->getId();
            $obj->name = $produit->getName();
            $obj->prix = $produit->getPrixVente();
            $obj->category = $produit->getCategory()->getNom();
            $res[] = $obj; 
        }

        return $this->json(
            ['produits' => $res], 
            200
        );
        
    }

    #[Route('/produit/{id}/edit', 'produit.edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Produit $produit, Request $request, EntityManagerInterface $em, PrixVenteRepository $prixVenteRepository)
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prixTem = new PrixVente();
            $prixTem->setStatus(1);
            $prixTem->setValeur($form->get('Prix')->getData());
            $prixTem->setProduit($produit);

            $prixVenteRepository->setStatusToZero($produit, 0);

            $em->persist($prixTem);
            $em->persist($produit);
            $em->flush();
            $this->addFlash("success", 'Le produit a bien été modifié');
            return $this->redirectToRoute('produit.index');
        }
        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form
        ]);
    }

    #[Route('/produit/add', 'produit.add')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, EntityManagerInterface $em, SessionInterface $session): RedirectResponse|Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prixTem = new PrixVente();
            $prixTem->setStatus(1);
            $prixTem->setValeur($form->get('Prix')->getData());
            $prixTem->setProduit($produit);
            $em->persist($prixTem);
            $em->persist($produit);
            try {
                $em->flush();
                $this->addFlash('success', 'Le produit a été bien créé');
                return $this->redirectToRoute('produit.index');
            } catch (\Throwable $th) {
                $this->addFlash('danger', 'Produit déja existante.');
                return $this->redirectToRoute('produit.add', []);
            }
        }
        return $this->render('produit/add.html.twig', [
            'form' => $form,
        ]);
    }
}
