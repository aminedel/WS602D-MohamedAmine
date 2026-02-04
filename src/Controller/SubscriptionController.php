<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/subscription')]
class SubscriptionController extends AbstractController
{
    #[Route('/', name: 'app_subscription')]
    public function index(PlanRepository $planRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        $plans = $planRepository->findActivePlans();

        return $this->render('subscription/index.html.twig', [
            'user' => $user,
            'plans' => $plans,
            'currentPlan' => $user->getPlan(),
        ]);
    }

    #[Route('/change/{planId}', name: 'app_subscription_change', methods: ['POST'])]
    public function change(
        int $planId,
        PlanRepository $planRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        $newPlan = $planRepository->find($planId);

        if (!$newPlan || !$newPlan->isActive()) {
            $this->addFlash('error', 'Le plan sélectionné n\'existe pas ou n\'est pas disponible.');
            return $this->redirectToRoute('app_subscription');
        }

        // Check if it's the same plan
        if ($user->getPlan() && $user->getPlan()->getId() === $newPlan->getId()) {
            $this->addFlash('info', 'Vous êtes déjà abonné à ce plan.');
            return $this->redirectToRoute('app_subscription');
        }

        // Update user's plan
        $user->setPlan($newPlan);

        // Update user role if plan has specific role
        if ($newPlan->getRole()) {
            $roles = $user->getRoles();
            // Remove old plan roles
            $roles = array_filter($roles, fn($role) => !str_starts_with($role, 'ROLE_PREMIUM'));
            if ($newPlan->getRole() !== 'ROLE_USER') {
                $roles[] = $newPlan->getRole();
            }
            $user->setRoles(array_unique($roles));
        }

        $entityManager->flush();

        $this->addFlash('success', sprintf('Votre abonnement a été changé avec succès vers le plan %s !', $newPlan->getName()));

        return $this->redirectToRoute('app_subscription');
    }
}
