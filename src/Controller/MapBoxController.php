<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapBoxController extends AbstractController
{
    #[Route('/mapbox', name: 'app_map_box', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('dashboard/itineaire.html.twig', [  ]);
    }
}

