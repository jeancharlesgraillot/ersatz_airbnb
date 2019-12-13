<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    /**
     * Permet d'afficher et de gérer le formulaire de connexion
     * 
     * @Route("/login", name="account_login")
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $utils) //La class AuthenticationUtils offre des utilitaires concernant l'authentification 
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        return $this->render('account/login.html.twig', [
            'hasError'=> $error !== null,
            'username' => $username
        ]);
    }
    /**
     * Permet de se déconnecter
     *
     *@Route("/logout", name="account_logout") 
     * 
     * @return void
     */
    public function logout(){
        //...rien à mettre
    }

    /**
     * Permet d'afficher le formulaire d'inscription
     * 
     * @Route("/register", name="account_register")
     *
     * @return Response
     */
    public function register(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){

        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //La méthode encodePassword permet d'encoder un password avec l'algorithme décrit dans les le security.yaml
            $hash = $encoder->encodePassword($user, $user->getHash());

            $user->setHash($hash);

            $manager->persist($user);

            $manager->flush();

            $this->addFlash(
                'success',
                "Votre compte a bien été créé ! Vous pouvez maintenant vous connecter !"
            );

            return $this->redirectToRoute("account_login");
        }

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher et de traiter le formulaire de modification de profil
     *
     * @Route("/account/profile", name="account_profile")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function profile(Request $request, ObjectManager $manager){

        //La fonction getUser du controller permet de récupérer l'utilisateur connecté

        $user = $this->getUser();

        $form = $this->createForm(AccountType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Il n'est normalement pas nécessaire de faire persister une entité qui existe déjà
            $manager->persist($user);

            $manager->flush();

            $this->addFlash(
                'success',
                "Les données du profil ont été enregistrées avec succès !"
            );
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier le mot de passe
     *
     * @Route("/account/password-update", name="account_password")
     * @IsGranted("ROLE_USER")
     * 
     * @return response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder, ObjectManager $manager){

        $passwordUpdate = new PasswordUpdate();

        $user = $this->getUser();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Vérifier que le oldPassword du formulaire soit le même que celui du User
            //Fonction php password_verify: le 1er paramètre est le password en clair et le 2ème un hash; ça vérifie que l'un correspond à l'autre et renvoie true si c'est le cas
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getHash())) {
                //Gérer l'erreur (api form dans la doc symfony)
                //Ces méthode permet d'accéder à un champ particulier du formulaire et d'y ajouter une erreur
                $form->get('oldPassword')->addError(new FormError("Le mot de passe saisi n'est pas votre mot de passe actuel !"));
            }else{
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->encodePassword($user, $newPassword);//1er paramètre: l'entité pour laquelle je veux encoder; 2ème: le password en question
                
                $user->setHash($hash);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                'success',
                'Votre mot de passe a bien été modifié !'
                );

                return $this->redirectToRoute('homepage');
            } 
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * Permet d'afficher le profil de l'utilisateur connecté
     *
     * @Route("/account", name="account_index")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function myAccount(){
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * Permet d'afficher la liste des réservations faites par l'utilisateur
     *
     * @Route("/account/bookings", name="account_bookings")
     * 
     * @return Response
     */
    public function bookings(){
        return $this->render('account/bookings.html.twig');
    }
}

// Pour le controlleur du login, rien a préciser, tout est configuré dans security.yaml