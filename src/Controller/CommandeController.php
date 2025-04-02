<?php

namespace App\Controller;

use App\Entity\CommandeProduit;
use App\Entity\Commande;
use App\Repository\CommandeProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'commande.index')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, CommandeRepository $repository): Response
    {

        $commande = $repository->findAll();
        return $this->render('commande/index.html.twig', [
            'commandes' => $commande,
        ]);
    }

    #[Route('/commande/status/{id}', name: 'commande.status')]
    #[IsGranted('ROLE_USER')]
    public function commandeByStatus(int $id, Request $request, CommandeProduitRepository $commandeProduitRepository): Response
    {
        // 0 = En cours
        // 1 = En attente
        // 2 = Validée
        // 3 = Livrée
        // 4 = Annulée

        $message = match ($id) {
            0 => "En cours",
            1 => "En attente",
            2 => "Validée",
            3 => "Livrée",
            4 => "Annulée",
        };

        $commandesProduits = $commandeProduitRepository->findProductByStatusAndSumQuantities($message);
        return $this->render('commande/status.html.twig', [
            'commandesProduits' => $commandesProduits,
            'status' => $message,
        ]);
    }



    #[Route('/commande/{id}/edit', 'commande.edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Commande $commande, Request $request, EntityManagerInterface $em, CommandeProduitRepository $commandeProduitRepository)
    {

        $commandeProduits = $commandeProduitRepository->findByCommand($commande->getId());
        if ($request->getMethod() == "POST") {
            $commande->setReference($request->get('reference'));
            $commande->setDescription($request->get('description'));
            $em->persist($commande);
            $listProduit = $request->get('produit');
            $listStatus = $request->get('status');
            $listQuantiteReel = $request->get('quantityreel');
            $listPrixReel = $request->get('prixreel');
            $i = 0;
            if ($listProduit) {
                foreach ($listProduit as $prod) {
                    if ($commandeProduits[$i]->getProduit()->getId() == $prod) {
                        $commandeProduits[$i]->setStatus($listStatus[$i]);
                        $commandeProduits[$i]->setPrixReel($listPrixReel[$i] ? $listPrixReel[$i] : 0);
                        $commandeProduits[$i]->setQuantityReel($listQuantiteReel[$i] ? $listQuantiteReel[$i] : 0);
                        if ($commandeProduits[$i]->getStatus() == 'Livrée') {
                            $commandeProduits[$i]->setValidateBy($this->getUser());
                        }
                    }
                    $em->persist($commandeProduits[$i]);
                    $i++;
                }
            }
            $em->flush();

            $this->addFlash("success", 'Le Commande ' . $commande->getReference() . ' a bien été enregistré');
            return $this->redirectToRoute('commande.index');
        }
        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'commandeProduits' => $commandeProduits
        ]);
    }

    #[Route('/commande/add', 'commande.add')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, EntityManagerInterface $em, SessionInterface $session, UserRepository $Userrepository, ProduitRepository $Produitrepository, CommandeProduitRepository $commandeProduitRepository): RedirectResponse|Response
    {
        $commande = new Commande();

        $users = $Userrepository->findAll();
        $produits = $Produitrepository->findAll();
        if ($request->getMethod() == 'POST') {
            $commande->setReference($request->get('reference'));
            $commande->setDescription($request->get('description'));
            $created = $this->getUser(); // Récupère l'utilisateur connecté
            $user = $Userrepository->find($request->get('user'));
            $commande->setUser($user);
            $commande->setCreatedBy($created);
            $em->persist($commande);
            $listProduit = $request->get('produit');
            $listQuantite = $request->get('quantity');
            $listPrix = $request->get('prixunitaire');
            $listStatus = $request->get('status');
            $i = 0;
            foreach ($listProduit as $prod) {
                // Vérifier si le produit est déjà associé à cette vente
                $produit = $Produitrepository->find($prod);
                $commandeProduitExist = $commandeProduitRepository->findOneBy([
                    'commande' => $commande,
                    'Produit' => $produit
                ]);

                if ($commandeProduitExist) {
                    $commandeProduitExist->setQuantity($commandeProduitExist->getQuantity() + $listQuantite[$i]);
                    $em->persist($commandeProduitExist);
                } else {
                    $commandeProduit = new CommandeProduit();
                    $produit = $Produitrepository->find($prod);
                    $commandeProduit->setCommande($commande);
                    $commandeProduit->setProduit($produit);
                    $commandeProduit->setQuantity($listQuantite[$i]);
                    $commandeProduit->setPrixunitaire($listPrix[$i]);
                    $commandeProduit->setStatus($listStatus[$i]);
                    $em->persist($commandeProduit);
                    $em->flush();
                }
                $i++;
            }
            $em->flush();

            $this->addFlash("success", 'Le Commande a bien été enregistré');
            return $this->redirectToRoute('commande.index');
        }
        return $this->render('commande/add.html.twig', [
            'users' => $users,
            'produits' => $produits
        ]);
    }
}
