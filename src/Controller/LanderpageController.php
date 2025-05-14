<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LanderpageController extends AbstractController
{
    #[Route('/', name: 'app_')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_inmobiliaria');
        
    }
    #[Route('/inmobiliaria', name: 'app_inmobiliaria')]
    public function landerpage(): Response
    {
        return $this->render('landerpage/index.html.twig', [
            'controller_name' => 'LanderpageController',
        ]);
    }
}
