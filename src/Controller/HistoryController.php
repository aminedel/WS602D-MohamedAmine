<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HistoryController extends AbstractController
{
    #[Route('/history', name: 'app_history')]
    public function index(GenerationRepository $generationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $generations = $generationRepository->findByUserOrderedByDate($user->getId());

        return $this->render('history/index.html.twig', [
            'generations' => $generations,
        ]);
    }
}
