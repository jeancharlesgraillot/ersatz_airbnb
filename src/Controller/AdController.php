<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//Créer un controller par la ligne de commandes: php bin/console make:controller NameController. Ajoute un controller et un fichier dans un dossier du nom du controller

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     * 
     * @return Response
     */
    public function index(AdRepository $repo)
    {
        //Ligne d'après commentée car on a ajouté les paramètres dans la fonction pour éviter d'avoir à écrire cette ligne
        // $repo = $this->getDoctrine()->getRepository(Ad::class);
        //bin/console debug:autowiring : liste des services qui peuvent être injectés

        $ads = $repo->findAll();

        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }


    /**
     * Permet de créer une annonce
     * 
     * @Route("/ads/new", name="ads_create")
     * @IsGranted("ROLE_USER") //Fait en sorte que seul un utilisateur connecté puisse créer une annonce
     *
     * @return Response
     */
    public function create(Request $request, ObjectManager $manager){   //Pour traiter la requète du formulaire envoyé il faut ajouter des paramètres request et ObjectManager dans la function

        $ad = new Ad();


        $form = $this->createForm(AnnonceType::class, $ad);
        // Le form est un objet qui permet de gérer toutes sortes de choses: validité du formulaire, sa soumission, les liens avec l'entité au moment de la soumission, 

        //On demande à form de traiter la requête
        $form->handleRequest($request);
        
        //On check si le formulaire a bien été soumis et sa validité
        if ($form->isSubmitted() && $form->isValid()){

            // Pour chaque image des images soumises
            foreach ($ad->getImages() as $image) {
                
                $image->setAd($ad); // On dit que l'image appartient à l'annonce
                
                $manager->persist($image); // On fait persister
            }
            
            $ad->setAuthor($this->getUser());

            $manager->persist($ad); //Prévient doctrine de sauvegarder la requête

            $manager->flush();  //Envoie la requête SQL

            // Ajouter un flash pour signaler quelque chose à l'utilisateur
            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée !"
            );

            return $this->redirectToRoute('ads_show', [
                'slug'=> $ad->getSlug()
            ]);
        }

        return $this->render('ad/new.html.twig', [
            'form' =>$form->createView()
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     *
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor() ", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier !") //Permet plus de flexibilité que@IsGranted() grâce aux expressions
     * 
     * @return response
     */
    public function edit(Ad $ad, Request $request, ObjectManager $manager){

        $form = $this->createForm(AnnonceType::class, $ad);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            foreach ($ad->getImages() as $image) {
                
                $image->setAd($ad); 
                
                $manager->persist($image);
            }

            $manager->persist($ad);

            $manager->flush();

            
            $this->addFlash(
                'success',
                "Les modifications de l'annonce <strong>{$ad->getTitle()}</strong> ont bien été enregistrées !"
            );

            return $this->redirectToRoute('ads_show', [
                'slug'=> $ad->getSlug()
            ]);
        }

        return $this->render('ad/edit.html.twig', [
            'form' => $form->createView(),
            'ad' => $ad
        ]);
    }

    //Attention de toujours placer les fonctions qui contiennent une route avec paramètre en dernier

    /**
     * Permet d'afficher une seule annonce
     *
     * @Route("/ads/{slug}", name="ads_show")
     * 
     * @return Response
     */
    public function show(Ad $ad){
        // Avec le ParamConverter, symfony fournit directement un objet en fonction du paramètre fourni dans la route

        //Avec les repository de Doctrine, on peut rechercher n'importe quel résultat en fonction de leurs champs
        //Dans le AdRepository, il y a un champ slug d'ou le findBySlug
        //Renvoie un tableau car il peut y avoir plusieurs résultats
        //$ad = $repo->findBySlug();
        //Ici, je récupère l'annonce qui correspond au slug 

        // Avec le ParamConverter, plus besoin de tous ces paramètres
        // public function show($slug, AdRepository $repo){}
        // $ad = $repo->findOneBySlug($slug);
        

        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }

    /**
     * Permet de supprimer une annonce
     *
     * 
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Vous n'avez pas le droit d'accéder à cette ressource !")
     * 
     * @param Ad $ad
     * @param ObjectManager $manager
     * @return response
     */
    public function delete(Ad $ad, ObjectManager $manager){
        $manager->remove($ad);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !"
        );

        return $this->redirectToRoute("ads_index");
    }
}
