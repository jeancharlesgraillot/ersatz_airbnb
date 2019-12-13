<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * Dans la doc sur le routing, le requirement permet de placer une contrainte en paramètre d'une route requirements={"page": "\d+"}
     * On peut à la place préciser ça dans la variable
     * Pour les 2 méthodes, on utilise une expression régulière, on ajoute ? pour dire que c optionnel et le 1 comme valeur par défaut pour la 2eme méthode
     * Pour la méthode requirements, il faut préciser la valeur de $page dans le paramètre de la fonction
     * 
     * @Route("/admin/ads/{page<\d+>?1}", name="admin_ads_index")
     */
    public function index(AdRepository $repo, $page, PaginationService $pagination)
    {
        // Méthode find qui permet de retrouver un enregistrement par son identifiant
        // $ad = $repo->find(332);
        
        // Méthode find qui permet de retrouver un enregistrement par plusieurs critères
        // $ad = $repo->findOneBy([
        //     'id' => 213,
        //     'title' => "annonce corrigée !"
            
        // ]);
        
        //Cette méthode demande critères de recherhe, ordre, limite, à partir de
        // $ads = $repo->findBy([], [], 5, 0);

        // $limit = 10;

        // $start = $page * $limit - $limit;
        // 1 * 10 - 10 = 0
        // 2 * 10 - 10 = 10 etc...
        
        $pagination->setEntityClass(Ad::class)
                    ->setPage($page);
                    
        return $this->render('admin/ad/index.html.twig', [
            // 'ads' => $pagination->getData(),
            // 'pages' => $pagination->getPages(),
            // 'page' => $page
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     *
     * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
     * 
     * @param Ad $ad
     * @return Response
     */
    public function edit(Ad $ad, Request $request, ObjectManager $manager){
        $form = $this->createForm(AnnonceType::class, $ad);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée !"
            );
        }
        return $this->render('admin/ad/edit.html.twig', [
            'ad' => $ad,
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer une annonce non conforme
     *
     *@Route("/admin/ads/{id}/delete", name="admin_ads_delete") 
     * 
     * @param Ad $ad
     * @param ObjectManager $manager
     * @return Response
     */
    public function delete(Ad $ad, ObjectManager $manager){
        if (count($ad->getBookings()) > 0) {
            $this->addFlash(
                'warning',
                "Vous ne pouvez pas supprimer l'annonce <strong>{$ad->getTitle()}</strong> car elle possède déjà des réservations !"
            );
        }else{
            $manager->remove($ad);
            $manager->flush();
    
            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !"
            );
        }

        return $this->redirectToRoute('admin_ads_index');
    }
}
