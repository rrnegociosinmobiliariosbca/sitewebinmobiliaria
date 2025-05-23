<?php

namespace App\Controller;

use App\Entity\PropertyDetalle;
use App\Entity\PropertyImage;
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
            $imagenes = array_map(fn($img) => $img->getUrl(), $prop->getPropertyImages()->toArray());
            if (empty($imagenes)) {
                $imagenes[] = '/img/Imagen_no_disponible.svg';
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
                'detalles' => array_map(fn($det) => $det->getTexto(), $prop->getPropertyDetalles()->toArray()),
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


        return $this->redirectToRoute('app_buscar_arrendo'); // Ajusta según tu ruta
    }

    #[Route('/imagen/agregar', name: 'agregar_imagen', methods: ['POST'])]
    public function agregar(Request $request, SluggerInterface $slugger, PropertyRepository $propertyRepo,HttpClientInterface $httpClient,PropertyImageRepository $imageRepository): Response
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
        }
        elseif ($imagenLink) {
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
        }
        else {
            return new Response('No se proporcionó imagen ni link', 400);
        }



        return $this->redirectToRoute('app_buscar_arrendo');







}
}
