<?php

namespace App\Controller;

use App\Repository\BienvenidoRepository;
use App\Repository\PropertyDetalleRepository;
use App\Repository\PropertyImageRepository;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Filesystem\Filesystem;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', []);
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


        // validar si id es mayor a 0 entonces es una actualización
        if (isset($data['id']) && $data['id'] > 0) {
            $property = $propertyRepository->find($data['id']);
            if (!$property) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Inmueble no encontrado',
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            // Si no se proporciona un ID, se crea un nuevo objeto Property
            $property = new \App\Entity\Property();
        }
        $property->setInmueble($data['inmueble']);
        $property->setValor($data['valor']);
        $property->setDireccion($data['direccion']);
        $property->setBarrio($data['barrio']);
        $property->setObservacion($data['observacion']);
        $property->setUbicacion($data['ubicacion']);
        $property->setTipoInmueble($data['tipo_inmueble']);
        $property->setCodigoInmueble($data['codigo_inmueble']);
        //tipo de contrato
        $property->setTipoContrato($data['tipo_contrato']);


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
                'tipo_contrato' => $property->getTipoContrato(),

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
                'tipo_contrato' => $property->getTipoContrato(),
            ];
        }


        return new JsonResponse($data);
    }


    #[Route('/dashboard/endpoint/eliminar_inmueble/{id}', name: 'app_dashboard_endpoint_eliminar_inmueble')]
    public function app_dashboard_endpoint_eliminar_inmueble(
        int $id,
        PropertyRepository $propertyRepository,
        PropertyDetalleRepository $propertyDetalleRepository,
        PropertyImageRepository $propertyImageRepository
    ): JsonResponse {
        // Buscar el registro por ID
        $property = $propertyRepository->find($id);

        if (!$property) {
            return $this->json([
                'status' => 'error',
                'message' => 'Inmueble no encontrado',
            ], Response::HTTP_NOT_FOUND);
        }

        //eliminar detalles asociados
        $propertyDetalles = $property->getPropertyDetalles();
        foreach ($propertyDetalles as $detalle) {
            $propertyDetalleRepository->remove($detalle);
        }
        //eliminar imagenes asociadas
        $propertyImages = $property->getPropertyImages();
        foreach ($propertyImages as $image) {
            //eliminar imagenes con unlink
            $propertyImageRepository->remove($image);
            $imagePath = $this->getParameter('imagenes_directory') . '/' . basename($image->getUrl());
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $propertyRepository->remove($property);

        return $this->json([
            'status' => 'success',
            'message' => 'Inmueble eliminado correctamente',
        ]);
    }

    // enpoint para guardar imagen musica y texto de bienvenida

    #[Route('/dashboard/endpoint/guardar_bienvenida', name: 'app_dashboard_endpoint_guardar_bienvenida', methods: ['POST'])]
    public function app_dashboard_endpoint_guardar_bienvenida(
        Request $request,
        BienvenidoRepository $bienvenidoRepository
    ): JsonResponse {
        $texto = $request->request->get('texto');
        /** @var UploadedFile|null $imagen */
        $imagen = $request->files->get('imagen');
        /** @var UploadedFile|null $audio */
        $audio = $request->files->get('audio');

        $bienvenido = $bienvenidoRepository->find(1) ?? new \App\Entity\Bienvenido();
        $fs = new Filesystem();

        $imgDir = $this->getParameter('imagenes_directory'); // /public/img

        // Guardar imagen como bienvenido.jpg
        if ($imagen) {
            $imgPath = $imgDir . '/bienvenido.jpg';
            if ($fs->exists($imgPath)) {
                $fs->remove($imgPath);
            }
            $imagen->move($imgDir, 'bienvenido.jpg');
            $bienvenido->setImagen('img/bienvenido.jpg');
        }

        // Guardar audio como bienvenido.mp3
        if ($audio) {
            $audioPath = $imgDir . '/bienvenido.mp3';
            if ($fs->exists($audioPath)) {
                $fs->remove($audioPath);
            }
            $audio->move($imgDir, 'bienvenido.mp3');
            $bienvenido->setAudio('img/bienvenido.mp3');
        }

        // Guardar texto si no está vacío
        if (!empty($texto)) {
            $bienvenido->setTexto($texto);
        }

        $bienvenidoRepository->save($bienvenido, true);

        return $this->json([
            'status' => 'success',
            'message' => 'Bienvenida guardada correctamente',
            'data' => [
                'imagen' => $bienvenido->getImagen(),
                'audio' => $bienvenido->getAudio(),
                'texto' => $bienvenido->getTexto(),
            ],
        ]);
    }
}
