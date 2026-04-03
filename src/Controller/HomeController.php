<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use App\Repository\PlanRepository;
use App\Repository\ToolRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PlanRepository $planRepository, ToolRepository $toolRepository): Response
    {
        $plans = $planRepository->findActivePlans();
        $tools = $toolRepository->findAll();

        return $this->render('home/index.html.twig', [
            'plans' => $plans,
            'tools' => $tools,
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(
        ToolRepository $toolRepository,
        GenerationRepository $generationRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $tools = $toolRepository->findAll();

        $todayCount = 0;
        if ($user instanceof \App\Entity\User) {
            $todayCount = $generationRepository->countPdfGeneratedByUserOnDate(
                (int) $user->getId(),
                new \DateTime('today'),
                new \DateTime('tomorrow')
            );
        }

        return $this->render('home/dashboard.html.twig', [
            'user' => $user,
            'tools' => $tools,
            'todayCount' => $todayCount,
        ]);
    }
}
