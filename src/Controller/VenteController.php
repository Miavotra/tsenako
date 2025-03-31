<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vente;
use App\Entity\VenteProduit;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use App\Repository\VenteProduitRepository;
use App\Repository\VenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

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
            $vente->setStatus($request->get('status'));
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
            $vente->setCommentaire($request->get('commentaire'));
            $vente->setStatus($request->get('status'));
            $em->persist($vente);
            $listProduit = $request->get('produit');
            $listQuantite = $request->get('quantity');
            $listPrix = $request->get('prixunitaire');
            $i = 0;
            $addProduit = true;
            foreach ($listProduit as $prod) {
                $produit = $produitRepository->find($prod);
                foreach($venteProduits as $vp) {
                    if ($vp->has($produit)) {
                        $vp->setQuantite($listQuantite[$i]);
                        $vp->setPrixVente($listPrix[$i]);
                        $addProduit = false;
                    }
                }

                if ($addProduit) {
                    $vp = new VenteProduit();
                    $produit = $produitRepository->find($prod);
                    $vp->setVente($vente);
                    $vp->setProduit($produit);
                    $vp->setQuantite($listQuantite[$i]);
                    $vp->setPrixVente($listPrix[$i]);

                }

                $i++;
                $em->persist($vp);
            }
            $em->flush();

            $this->addFlash("success", 'La vente ' . $vente->getReference() . ' a bien été enregistré');
            return $this->redirectToRoute('vente.index');
        }
        return $this->render('vente/edit.html.twig', [
            'vente' => $vente,
            'produits' => $produitRepository->findAll(),
            'venteProduits' => $venteProduits
        ]);
    }

    #[Route('/api/vente', name: 'api_vente_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function apiVenteAdd(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $em, UserRepository $userRepository, ProduitRepository $produitRepository): JsonResponse
    {

        try {
            $data = json_decode($request->getContent(), true);

            $vente = new Vente();
            $vente->setReference('vente-' . date('d-m-Y-His'));
            $vente->setCommentaire($data['vente']['commentaires']);
            $vente->setStatus('En cours');
            $vente->setUser($user);
            $em->persist($vente);

            $listProduit = $data['vente']['produits'];
            foreach ($listProduit as $prod) {
                $venteProduit = new VenteProduit();
                $produit = $produitRepository->find($prod['id']);
                if(empty($produit)) throw new Exception("Le produit n'existe pas. id = " . $prod['id']);
                $venteProduit->setVente($vente);
                $venteProduit->setProduit($produit);
                $venteProduit->setQuantite($prod['quantite']);
                $venteProduit->setPrixVente($prod['prixVente']);

                $em->persist($venteProduit);
            }
            $em->flush();

            return $this->json(
                ['code' => 201, 'message' => 'Created'], 
                201
            );

        } catch (Exception $e) {
            return $this->json(
                ['code' => 400, 'message' => $e->getMessage()], 
                400
            );
        }

        
    }

}
