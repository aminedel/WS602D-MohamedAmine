<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class HistoryController extends AbstractController
{
    #[Route('/history', name: 'app_history')]
    #[Route('/account/history', name: 'app_account_history')]
    public function index(GenerationRepository $generationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $generations = $generationRepository->findByUserOrderedByDate((int) $user->getId());

        return $this->render('history/index.html.twig', [
            'generations' => $generations,
        ]);
    }
}
