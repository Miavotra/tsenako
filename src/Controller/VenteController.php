<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VenteController extends AbstractController
{
    #[Route('/vente', name: 'vente.index')]
    public function index(): Response
    {
        return $this->render('vente/index.html.twig', [
            'controller_name' => 'VenteController',
        ]);
    }
}
