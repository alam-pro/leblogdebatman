<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Form\EditPhotoType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    /*
     *  Controller de la page d'accueil
     * */
    #[Route('/', name: 'main_home')]
    public function home(): Response
    {
        return $this->render('main/home.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    /*
     * Controller de la page mon profil
     *
     * Accès réservé ux connectés (ROLE_USER)
     * */
    #[Route('/mon-profil/', name:'main_profil')]
    #[IsGranted('ROLE_USER')]
    public function profil(): Response
    {
        return $this->render('main/mon-profil.html.twig');
    }

    /**
     * Contrôleur de la page de modification de la photo de profil
     */
    #[Route('/changer-photo-de-profil/', name: 'main_edit_photo')]
    #[IsGranted('ROLE_USER')]
    public function editPhoto(Request $request, ManagerRegistry $doctrine): Response
    {

        $form = $this->createForm(EditPhotoType::class);

        $form->handleRequest($request);

        // Si le formulaire a été envoyé et sans erreurs
        if ($form->isSubmitted() && $form->isValid()){

            $photo = $form->get('photo')->getData();

            $newFileName = 'user' . $this->getUser()->getId() . '.' . $photo->guessExtension();

            $this->getUser()->setPhoto($newFileName);
            $em =$doctrine->getManager();
            $em->flush();

            // Sauvegarde physique de la photo cote serveur
            $photo->move(
                $this->getParameter('app.user.photo.directory'),
                $newFileName
            );

            // Message de succès
            $this->addFlash('success', 'Photo de profil modifiée avec succès');

            // Redirection vers la page profil
            return $this->redirectToRoute('main_profil');
        }

        return $this->render('main/edit_photo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}


