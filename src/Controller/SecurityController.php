<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{


    /*
     *  Controller de la page de connexion
     * */
    #[Route(path: '/connexion/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        // Si l'utilisateur est deja connecté, on le redirige de force sur la page d'accueil de site
         if ($this->getUser()) {
             return $this->redirectToRoute('main_home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }


    /*
     *  Controller de la page déconnexion
     * */
    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {

        // Le code ici ne sera jamais lu, car la page de connexion est deja gérée en interne par la bundle security.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
