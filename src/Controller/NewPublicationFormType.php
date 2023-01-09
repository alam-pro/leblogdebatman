<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\RegistrationFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *  Préfixe de la route et du nom de toutes les pages de la partie blog du site
 */
#[Route('/blog', name: 'blog_')]
class NewPublicationFormType extends AbstractController

{
    /**
     *  Contrôleur de la page permettant de créer un nouvel article
     */
    #[Route('/nouvelle-publication/', name: 'new_publication')]
    #[IsGranted('ROLE_ADMIN')]
    public function newPublication(Request $request, ManagerRegistry $doctrine): Response
    {
        // Création d'un nouvel objet de la classe Article, vide pour le moment
        $newArticle = new Article();

        // Création d'un nouveau formulaire à partir de notre formulaire NewPublicationFormType et de notre nouvel
        // article
        $form = $this->createForm(RegistrationFormType::class, $newArticle);

        // Symfony va remplir $newArticle grâce aux données du formulaire envoyé (accessibles dans l'objet $request, c'est pour ça qu'on doit lui donner)
        $form->handleRequest($request);

        // Pour savoir si le formulaire a été envoyé, on a accès à cette condition :
        if($form->isSubmitted() && $form->isValid() ){

            $newArticle
                ->setPublicationDate(new \DateTime())
                ->setAuthor( $this->getUser() )
            ;

            dump($newArticle);

            // récupération du manager des entités et sauvegarde en BDD de $newArticle
            $em = $doctrine->getManager();

            $em->persist($newArticle);

            $em->flush();

            // Création d'un flash message de type "success"
            $this->addFlash('success', 'Article créé avec succès !');

            // Redirection de l'utilisateur sur la route "home" (la page d'accueil)
            return $this->redirectToRoute('main_home');


        }

        return $this->render('blog/new_publication.html.twig', [
            'newArticleForm' => $form->createView(),
        ]);
    }
}
