<?php

namespace App\Controller;

use App\Repository\AdRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController{

    //3 piliers : une fonction publique, une route, une réponse

    //Attention de toujours bien mentionner les composants desquels on se sert avec les use

    //Il peut y avoir une variable dans la route

    //Il peut y avoir plusieurs routes pour une même fonction du contrôleur

    //On peut mettre des contraintes de paramètres sur les routes: requirements={"prenom"="\d+"}


    /**
     * @Route("/hello/{prenom}/age/{age}", name="hello")
     * @Route("/hello", name="hello_base")
     * @Route("/hello/{prenom}", name="hello_prenom")
     */
    public function hello($prenom = "anonyme", $age = 0){
        
        return $this->render(
            'hello.html.twig',
            [
                'prenom' => $prenom,
                'age' => $age
            ]
            );

    }

    /**
     * @Route("/", name="homepage")
     */
    public function home(AdRepository $adRepo, UserRepository $userRepo){


        return $this->render(
            'home.html.twig',
            
            //Tableau de variables pour twig 
            [
                'ads' => $adRepo->findBestAds(3),
                'users' => $userRepo->findBestUsers(2)
            ]
        );
    }

}

?>