<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

 #[Route(path: '/register', name: 'app_login')]
    public function register(UserRepository $userRepository): JsonResponse
    {
       // crear dos variable uana para el usuario y otra para la contraseña
        $user = 'admin';  
        $password = 'admin';

        // Verificar si el usuario ya existe

        $existingUser = $userRepository->findOneBy(['username' => $user]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'El usuario ya existe'], Response::HTTP_CONFLICT);
        }


      //hashear la contraseña antes de guardarla

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        // crear un nuevo usuario
        $newUser = new User();
        $newUser->setUsername($user);
        $newUser->setPassword($hashedPassword);
        $newUser->setRoles(['ROLE_ADMIN']); // Asignar el rol de administrador
        // guardar el usuario en la base de datos
        $userRepository->save($newUser, true);      
         
        // retornar un JsonResponse con el usuario y la contraseña

        return new JsonResponse([
            'user' => $user,
            'password' => $password,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
