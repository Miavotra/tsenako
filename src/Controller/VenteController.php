<?php

namespace App\Controller;

use App\Entity\Vente;
use App\Entity\VenteProduit;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use App\Repository\VenteProduitRepository;
use App\Repository\VenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class VenteController extends AbstractController
{
    #[Route('/vente', name: 'vente.index')]
    #[IsGranted('ROLE_USER')]
    public function index(VenteRepository $repository): Response
    {
        $ventes = $repository->findAll();
        return $this->render('vente/index.html.twig', [
            'ventes' => $ventes,
        ]);
    }

    #[Route('/vente/add', 'vente.add')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, EntityManagerInterface $em, UserRepository $userRepository, ProduitRepository $produitRepository): RedirectResponse|Response
    {
        $vente = new Vente();

        $users = $userRepository->findAll();
        $produits = $produitRepository->findAll();
        if ($request->getMethod() == 'POST') {
            $vente->setReference($request->get('reference'));
            $vente->setCommentaire($request->get('commentaire'));
            $created = $this->getUser(); // Récupère l'utilisateur connecté
            $vente->setUser($created);
            $em->persist($vente);
            $listProduit = $request->get('produit');
            $listQuantite = $request->get('quantity');
            $listPrix = $request->get('prixunitaire');
            $i = 0;
            foreach ($listProduit as $prod) {
                $venteProduit = new VenteProduit();
                $produit = $produitRepository->find($listProduit[$i]);
                $venteProduit->setVente($vente);
                $venteProduit->setProduit($produit);
                $venteProduit->setQuantite($listQuantite[$i]);
                $venteProduit->setPrixVente($listPrix[$i]);
                $i++;
                $em->persist($venteProduit);

            }
            $em->flush();

            $this->addFlash("success", 'La vente a bien été enregistré');
            return $this->redirectToRoute('vente.index');
        }
        return $this->render('vente/add.html.twig', [
            'users' => $users,
            'produits' => $produits
        ]);
    }

    #[Route('/vente/edit/{id}', 'vente.edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Vente $vente, Request $request, EntityManagerInterface $em, VenteProduitRepository $venteProduitRepository, ProduitRepository $produitRepository)
    {

        $venteProduits = $venteProduitRepository->findByVente($vente->getId());
        if ($request->getMethod() == "POST") {
            $vente = new Vente();
            $vente->setReference($request->get('reference'));
            $vente->setCommentaire($request->get('commentaire'));
            $listProduit = $request->get('produit');
            $listQuantite = $request->get('quantity');
            $listPrix = $request->get('prixunitaire');
            $i = 0;
            foreach ($listProduit as $prod) {
                $venteProduit = new VenteProduit();
                $produit = $produitRepository->find($listProduit[$i]);
                $venteProduit->setVente($vente);
                $venteProduit->setProduit($produit);
                $venteProduit->setQuantite($listQuantite[$i]);
                $venteProduit->setPrixVente($listPrix[$i]);
                $i++;
                $em->persist($venteProduit);
            }
            $em->flush();

            $this->addFlash("success", 'La vente ' . $vente->getReference() . ' a bien été enregistré');
            return $this->redirectToRoute('vete.index');
        }
        return $this->render('vente/edit.html.twig', [
            'vente' => $vente,
            'venteProduits' => $venteProduits
        ]);
    }
}
