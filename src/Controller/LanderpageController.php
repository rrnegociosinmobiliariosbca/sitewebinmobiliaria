<?php

namespace App\Controller;

use App\Entity\PropertyDetalle;
use App\Entity\PropertyImage;
use App\Repository\BienvenidoRepository;
use App\Repository\PropertyDetalleRepository;
use App\Repository\PropertyImageRepository;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class LanderpageController extends AbstractController
{
    #[Route('/', name: 'app_')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_inmobiliaria');
    }

    #[Route('/inmobiliaria', name: 'app_inmobiliaria')]
    public function landerpage(BienvenidoRepository $bienvenidoRepository): Response
    {
        // Obtener el objeto Bienvenido desde la base de datos
        $bienvenido = $bienvenidoRepository->find(1);
        if (!$bienvenido) {
            // Si no existe, crear un nuevo objeto Bienvenido
            $bienvenido = new \App\Entity\Bienvenido();
            $bienvenido->setTexto('¡Bienvenido a la aplicación!');
            $bienvenido->setImagen('img/bienvenido.jpg');
            $bienvenido->setAudio('img/bienvenido.mp3');
            $bienvenidoRepository->save($bienvenido, true);
        }

        // Renderizar la vista del dashboard con el objeto Bienvenido

        return $this->render('landerpage/landing.html.twig', [
            'bienvenido' => $bienvenido,
        ]);
    }
    #[Route('/buscar', name: 'app_buscar')]
    public function buscar(): Response
    {
        return $this->render('landerpage/buscar.html.twig');
    }
    #[Route('/buscar/ventas', name: 'app_buscar_ventas')]
    public function ventas(PropertyRepository $propertyRepository): Response
    {
        $properties = $propertyRepository->findByTipoContrato('venta');

        $data = [];

        foreach ($properties as $prop) {

            $imagenes = array_map(fn($img) => [
                'id' => $img->getId(),
                'url' => $img->getUrl()
            ], $prop->getPropertyImages()->toArray());

            if (empty($imagenes)) {
                $imagenes[] = [
                    'id' => 0,
                    'url' => '/img/Imagen_no_disponible.svg'
                ];
            }
            $data[] = [
                'id' => $prop->getId(),
                'inmueble' => $prop->getInmueble(),
                'valor' => $prop->getValor(),
                'direccion' => $prop->getDireccion(),
                'barrio' => $prop->getBarrio(),
                'observacion' => $prop->getObservacion(),
                'ubicacion' => $prop->getUbicacion(),
                'imagenes' => $imagenes,
                'detalles' => array_map(fn($det) => [
                    'id' => $det->getId(),
                    'texto' => $det->getTexto()
                ], $prop->getPropertyDetalles()->toArray()),
            ];
        }
        return $this->render('landerpage/ventas.html.twig', ['cards' => $data]);
    }

    #[Route('/ver/inmueble/{id}', name: 'app_ver_inmueble')]
    public function ver_inmueble(int $id, PropertyRepository $propertyRepository): Response
    {
        $prop = $propertyRepository->find($id);

        if (!$prop) {
            //rebderisa un json con un mensaje de error
            return $this->json(['error' => 'inmueble no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $data = [];
        $imagenes = array_map(fn($img) => [
            'id' => $img->getId(),
            'url' => $img->getUrl()
        ], $prop->getPropertyImages()->toArray());

        if (empty($imagenes)) {
            $imagenes[] = [
                'id' => 0,
                'url' => '/img/Imagen_no_disponible.svg'
            ];
        }
        $data[] = [
            'id' => $prop->getId(),
            'inmueble' => $prop->getInmueble(),
            'valor' => $prop->getValor(),
            'direccion' => $prop->getDireccion(),
            'barrio' => $prop->getBarrio(),
            'observacion' => $prop->getObservacion(),
            'ubicacion' => $prop->getUbicacion(),
            'codigoinmueble' => $prop->getCodigoInmueble(),
            'tipoinmueble' => $prop->getTipoInmueble(),
            'tipocontrato' => $prop->getTipoContrato(),
            'imagenes' => $imagenes,
            'detalles' => array_map(fn($det) => [
                'id' => $det->getId(),
                'texto' => $det->getTexto()
            ], $prop->getPropertyDetalles()->toArray()),
        ];

        return $this->render('dashboard/visorinmueble.html.twig', ['cards' => $data]);
    }
    #[Route('/buscar/arriendo', name: 'app_buscar_arrendo')]
    public function arrendo(PropertyRepository $propertyRepository): Response
    {
        $properties = $propertyRepository->findByTipoContrato('arriendo');

        $data = [];

        foreach ($properties as $prop) {

            $imagenes = array_map(fn($img) => [
                'id' => $img->getId(),
                'url' => $img->getUrl()
            ], $prop->getPropertyImages()->toArray());

            if (empty($imagenes)) {
                $imagenes[] = [
                    'id' => 0,
                    'url' => '/img/Imagen_no_disponible.svg'
                ];
            }
            $data[] = [
                'id' => $prop->getId(),
                'inmueble' => $prop->getInmueble(),
                'valor' => $prop->getValor(),
                'direccion' => $prop->getDireccion(),
                'barrio' => $prop->getBarrio(),
                'observacion' => $prop->getObservacion(),
                'ubicacion' => $prop->getUbicacion(),
                'imagenes' => $imagenes,
                'detalles' => array_map(fn($det) => [
                    'id' => $det->getId(),
                    'texto' => $det->getTexto()
                ], $prop->getPropertyDetalles()->toArray()),
            ];
        }



        return $this->render('landerpage/arrendo.html.twig', ['cards' => $data]);
    }
    #[Route('/property-detalle/create', name: 'app_propertydetalle_create', methods: ['POST'])]
    public function createDetalle(Request $request, PropertyRepository $propertyRepo, PropertyDetalleRepository $detalleRepository): Response
    {
        $propertyId = $request->request->get('property_id');
        $detalleTexto = $request->request->get('detalle');

        $property = $propertyRepo->find($propertyId);
        if (!$property) {
            throw $this->createNotFoundException("Propiedad no encontrada.");
        }

        $detalle = new PropertyDetalle();
        $detalle->setTexto($detalleTexto);
        $detalle->setProperty($property);
        $detalleRepository->save($detalle);


        if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), 'arriendo')) {
            return $this->redirectToRoute('app_buscar_arrendo'); // Ajusta según tu ruta
        }
        return $this->redirectToRoute('app_buscar_ventas'); // Ajusta según tu ruta
    }

    #[Route('/imagen/agregar', name: 'agregar_imagen', methods: ['POST'])]
    public function agregar(Request $request, SluggerInterface $slugger, PropertyRepository $propertyRepo, HttpClientInterface $httpClient, PropertyImageRepository $imageRepository): Response
    {
        $cardId = $request->request->get('cardId');
        $imagenLink = $request->request->get('imagenLink');
        /** @var UploadedFile $imagenArchivo */
        $imagenArchivo = $request->files->get('imagenArchivo');

        $inmueble = $propertyRepo->find($cardId);
        if (!$inmueble) {
            return new Response('Inmueble no encontrado', 404);
        }

        $imagen = new PropertyImage();
        $imagen->setProperty($inmueble);
        if ($imagenArchivo) {
            $newFilename = uniqid() . '.' . $imagenArchivo->guessExtension();
            try {
                $imagenArchivo->move(
                    $this->getParameter('imagenes_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                return new Response($e->getMessage(), 500);
            }

            $imagen->setUrl("/img/" . $newFilename);
            $imageRepository->save($imagen);
        } elseif ($imagenLink) {
            // Validar link
            try {
                $response = $httpClient->request('HEAD', $imagenLink);

                $statusCode = $response->getStatusCode();
                $contentType = $response->getHeaders(false)['content-type'][0] ?? '';

                if ($statusCode !== 200 || !str_starts_with($contentType, 'image/')) {
                    return new Response('El link no es válido o no apunta a una imagen', 400);
                }

                $imagen->setUrl($imagenLink);
                $imageRepository->save($imagen);
            } catch (\Exception $e) {
                return new Response('Error al validar el link: ' . $e->getMessage(), 400);
            }
        } else {
            return new Response('No se proporcionó imagen ni link', 400);
        }


        if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), 'arriendo')) {
            return $this->redirectToRoute('app_buscar_arrendo'); // Ajusta según tu ruta
        }
        return $this->redirectToRoute('app_buscar_ventas'); // Ajusta según tu ruta
    }

    //eliminar porpetyDetalle
    #[Route('/property-detalle/delete/{id}', name: 'app_propertydetalle_delete')]
    public function deleteDetalle(int $id, Request $request, PropertyDetalleRepository $detalleRepository): Response
    {
        $detalle = $detalleRepository->find($id);
        if (!$detalle) {
            throw $this->createNotFoundException("Detalle no encontrado.");
        }

        $detalleRepository->remove($detalle);

        if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), 'arriendo')) {
            return $this->redirectToRoute('app_buscar_arrendo'); // Ajusta según tu ruta
        }
        return $this->redirectToRoute('app_buscar_ventas'); // Ajusta según tu ruta
    }

    //eliminar imagen
    #[Route('/imagen/eliminar/{id}', name: 'app_imagen_eliminar')]
    public function eliminarImagen(int $id, Request $request, PropertyImageRepository $imageRepository): Response
    {
        //si el id de la imagen es null, no se elimina
        if ($id === 0) {
            return $this->redirectToRoute('app_buscar_arrendo'); // Ajusta según tu ruta
        }
        $imagen = $imageRepository->find($id);
        if (!$imagen) {
            throw $this->createNotFoundException("Imagen no encontrada.");
        }
        //eliminar imagen
        // Eliminar el archivo físico si es necesario
        $imagePath = $this->getParameter('imagenes_directory') . '/' . basename($imagen->getUrl());
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        // Eliminar la entidad de la base de datos
        $imageRepository->remove($imagen);


        if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), 'arriendo')) {
            return $this->redirectToRoute('app_buscar_arrendo'); // Ajusta según tu ruta
        }
        return $this->redirectToRoute('app_buscar_ventas'); // Ajusta según tu ruta
    }
}
