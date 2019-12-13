<?php

namespace App\Controller;

use App\Service\StatsService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDashboardController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(ObjectManager $manager, StatsService $statsService)
    {
        // La méthode createQuery permet d'écrire une requête dql sous forme de string
        // En dql on ne s'intéresse pas aux tables mais aux entités
        // Méthode getSingleScalarResult pour avoir le résultat sous forme d'une variable scalaire simple
        $stats = $statsService->getStats();
        $bestAds = $statsService->getAdsStats('DESC');
        $worstAds = $statsService->getAdsStats('ASC');    

        return $this->render('admin/dashboard/index.html.twig', [
            // La méthode compact de php renvoie la valeur associée aux clés entre parenthèse
            'stats' => $stats,
            'bestAds' => $bestAds,
            'worstAds' => $worstAds
        ]);
    }
}
