<?php

namespace App\Controller;

use App\Repository\PropertyRepository;
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
        return $this->render('landerpage/index.html.twig');
    }
    #[Route('/buscar', name: 'app_buscar')]
    public function buscar(): Response
    {
        return $this->render('landerpage/buscar.html.twig');
    }
    #[Route('/buscar/ventas', name: 'app_buscar_ventas')]
    public function ventas(): Response
    {
        return $this->render('landerpage/ventas.html.twig');
    }
    #[Route('/buscar/arrendo', name: 'app_buscar_arrendo')]
    public function arrendo(PropertyRepository $propertyRepository): Response
    {
        $properties = $propertyRepository->findAll();

        $data = [];

        foreach ($properties as $prop) {
            $data[] = [
                'inmueble' => $prop->getInmueble(),
                'valor' => $prop->getValor(),
                'direccion' => $prop->getDireccion(),
                'barrio' => $prop->getBarrio(),
                'observacion' => $prop->getObservacion(),
                'ubicacion' => $prop->getUbicacion(),
                'imagenes' => array_map(fn($img) => $img->getUrl(), $prop->getPropertyImages()->toArray()),
                'detalles' => array_map(fn($det) => $det->getTexto(), $prop->getPropertyDetalles()->toArray()),
            ];
        }

        return $this->render('landerpage/arrendo.html.twig', ['cards' => $data]);
    }
}
