<?php

namespace App\Controller;

use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/dashboard/endpoint/registrar_inmueble', name: 'app_dashboard_endpoint_registrar_inmueble')]
    public function app_dashboard_endpoint_registrar_inmueble(Request $request, PropertyRepository $propertyRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error al decodificar el JSON',
            ], Response::HTTP_BAD_REQUEST);
        }


        $property = new \App\Entity\Property();
        $property->setInmueble($data['inmueble']);
        $property->setValor($data['valor']);
        $property->setDireccion($data['direccion']);
        $property->setBarrio($data['barrio']);
        $property->setObservacion($data['observacion']);
        $property->setUbicacion($data['ubicacion']);
        $property->setTipoInmueble($data['tipo_inmueble']);
        $property->setCodigoInmueble($data['codigo_inmueble']);

        //guardar el objeto en la base de datos
        $propertyRepository->save($property);
        // Aquí puedes agregar la lógica para guardar el objeto en la base de datos 

        // Respuesta de éxito
        $response = [
            'status' => 'success',
            'message' => 'Inmueble registrado correctamente',
            'data' => [
                'inmueble' => $property->getInmueble(),
                'valor' => $property->getValor(),
                'direccion' => $property->getDireccion(),
                'barrio' => $property->getBarrio(),
                'observacion' => $property->getObservacion(),
                'ubicacion' => $property->getUbicacion(),
                'tipo_inmueble' => $property->getTipoInmueble(),
                'codigo_inmueble' => $property->getCodigoInmueble(),
            ],
        ];
        return $this->json($response);
    }
    #[Route('/dashboard/endpoint/list_inmueble', name: 'app_dashboard_endpoint_list_inmueble')]
    public function app_dashboard_endpoint_list_inmueble(PropertyRepository $propertyRepository): JsonResponse
    {
        // Obtener todos los registros de la tabla Property
        $properties = $propertyRepository->findAll();

        // Convertir los registros a un array
        $data = [];
        foreach ($properties as $property) {
            $data[] = [
                'id' => $property->getId(),
                'inmueble' => $property->getInmueble(),
                'valor' => $property->getValor(),
                'direccion' => $property->getDireccion(),
                'barrio' => $property->getBarrio(),
                'observacion' => $property->getObservacion(),
                'ubicacion' => $property->getUbicacion(),
                'tipo_inmueble' => $property->getTipoInmueble(),
                'codigo_inmueble' => $property->getCodigoInmueble(),
            ];
        }


        return new JsonResponse($data);
    }


}
