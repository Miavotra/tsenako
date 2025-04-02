<?php

namespace App\Controller;

use App\Entity\CommandeProduit;
use App\Form\CommandeType;
use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\User;
use App\Repository\CommandeProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\PrixVenteRepository;
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
use Symfony\Component\Validator\Constraints\Length;


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

    #[Route('/commande/{id}/edit', 'commande.edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Commande $commande, Request $request, EntityManagerInterface $em, CommandeProduitRepository $commandeProduitRepository)
    {

        $commandeProduits = $commandeProduitRepository->findByCommand($commande->getId());
        if ($request->getMethod() == "POST") {
            $commande = new Commande();
            $commande->setReference($request->get('reference'));
            $commande->setDescription($request->get('description'));
            $listProduit = $request->get('produit');
            $listStatus = $request->get('status');
            $listCommande = $request->get('commande');
            $listQuantiteReel = $request->get('quantityreel');
            $listPrixReel = $request->get('prixreel');
            $i = 0;
            if($listProduit) {
                foreach ($listProduit as $prod) {
                    if ($listStatus[$i] == -1) {
                        $em->remove($commandeProduits[$i]);
                        $em->flush();
                        continue;
                    }
                    if ($commandeProduits[$i]->getCommande()->getId() == $prod) {
                        $commandeProduits[$i]->setStatus($listStatus[$i]);
                        $commandeProduits[$i]->setPrixReel($listPrixReel[$i]);
                        $commandeProduits[$i]->setQuantityReel($listQuantiteReel[$i]);
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
    public function add(Request $request, EntityManagerInterface $em, SessionInterface $session, UserRepository $Userrepository, ProduitRepository $Produitrepository): RedirectResponse|Response
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
                $commandeProduit = new CommandeProduit();
                $produit = $Produitrepository->find($listProduit[$i]);
                $commandeProduit->setCommande($commande);
                $commandeProduit->setProduit($produit);
                $commandeProduit->setQuantity($listQuantite[$i]);
                $commandeProduit->setPrixunitaire($listPrix[$i]);
                $commandeProduit->setStatus($listStatus[$i]);
                $i++;
                $em->persist($commandeProduit);

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
