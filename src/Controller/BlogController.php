<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\NewPublicationFormType;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 *  Préfixe de la route et du nom de toutes les pages de la partie blog du site
 */
#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController

{
    /**
     * Contrôleur de la page permettant de créer un nouvel article
     */
    #[Route('/nouvelle-publication/', name: 'new_publication')]
    #[IsGranted('ROLE_ADMIN')]
    public function newPublication(Request $request, ManagerRegistry $doctrine): Response
    {

        // Création d'un nouvel article vide
        $newArticle = new Article();

        // Création d'un formulaire de création d'article, lié à l'article vide
        $form = $this->createForm(NewPublicationFormType::class, $newArticle);

        // Liaison des données POST aux formulaires
        $form->handleRequest($request);

        // Si le formulaire a bien été envoyé et sans erreurs
        if($form->isSubmitted() && $form->isValid()){


            // Hydrater l'article
            $newArticle
                ->setPublicationDate( new \DateTime() )     // Date actuelle
                ->setAuthor( $this->getUser() )       // Auteur de l'article (la personne actuellement connectée)
            ;

            // Sauvegarde de l'article en BDD
            $em = $doctrine->getManager();
            $em->persist( $newArticle );
            $em->flush();

            // Message de succès
            $this->addFlash('success', 'Article publié avec succès !');

            // Redirection de l'utilisateur vers l'article qu'il vient de créer
            return  $this->redirectToRoute('blog_publication_view', [
                'slug' => $newArticle->getSlug(),
            ]);

        }

        return $this->render('blog/new_publication.html.twig', [
            'new_publication_form' => $form->createView(),
        ]);
    }


    /*
     * Contrôleur de la page permettent de créer un nouvel article
     * */
    #[Route('/publications/liste/', name: 'publication_list')]
    public function publicationList(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): response
    {

        // Récupération du numéro de la page demendée dans l'url
        $requestedPge = $request->query->getInt('page', 1);

        // Vérification que le numéro est positif
        if ($requestedPge < 1){
            throw new NotFoundHttpException();
        }

        // Manager général des entités
        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');

        // Récupération des articles
        $articles = $paginator->paginate(
            $query,
            $requestedPge,
            10
        );

        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles, // On envoie les articles à la vue Twig
        ]);
    }


    /*
     * Contrôleur de la page permettant de voir n article en détail
     * */
    #[Route('/publication/{slug}/', name: 'publication_view')]
    public function publicationView(Article $article): Response
    {
        dump($article);

        return $this->render('blog/publication_view.html.twig', [
            'article' => $article,
        ]);
    }

}
