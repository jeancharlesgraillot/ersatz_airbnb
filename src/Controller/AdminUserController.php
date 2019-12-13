<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * Permet d'afficher les utilisateurs
     *
     * @Route("/admin/users/{page<\d+>?1}", name="admin_users_index")
     * 
     * @param UserRepository $repo
     * @return Response
     */
    public function index(UserRepository $repo, $page, PaginationService $pagination)
    {
        $pagination->setEntityClass(User::class)
                    ->setLimit(5)
                    ->setPage($page);
                    
        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet de modifier le profil utilisateur
     *
     * @Route("admin/users/{id}/edit", name="admin_user_edit")
     * 
     * @param User $user
     * @return Response
     */
    public function edit(User $user, Request $request, ObjectManager $manager){

        $form = $this->createForm(AdminUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le profil utilisateur de <strong>{$user->getFullName()}</strong> a bien été mis à jour !"
            );

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

}
