<?php

namespace App\Controller;

use App\Repository\PlanRepository;
use App\Repository\UserRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebhookController extends AbstractController
{
    #[Route('/payment/webhook', name: 'app_payment_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        StripeService $stripeService,
        UserRepository $userRepository,
        PlanRepository $planRepository,
        EntityManagerInterface $em,
    ): Response {
        $payload = $request->getContent();
        $sigHeader = (string) $request->headers->get('Stripe-Signature');

        // Verify Stripe signature
        try {
            $event = $stripeService->constructWebhookEvent($payload, $sigHeader);
        } catch (SignatureVerificationException $e) {
            return new Response('Signature invalide', Response::HTTP_BAD_REQUEST);
        }

        // Handle event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;

                $userId = $session->metadata->user_id ?? null;
                $planId = $session->metadata->plan_id ?? null;

                if (!$userId || !$planId) {
                    return new Response('Metadata manquantes', Response::HTTP_BAD_REQUEST);
                }

                $user = $userRepository->find($userId);
                $plan = $planRepository->find($planId);

                if (!$user || !$plan) {
                    return new Response('Utilisateur ou plan introuvable', Response::HTTP_NOT_FOUND);
                }

                // Update user's plan
                $user->setPlan($plan);

                // Update role based on plan
                if ($plan->getRole() && $plan->getRole() !== 'ROLE_USER') {
                    $roles = ['ROLE_USER', $plan->getRole()];
                    $user->setRoles(array_unique($roles));
                }

                $em->flush();
                break;

            case 'customer.subscription.deleted':
                // Subscription cancelled - revert to FREE plan
                $subscription = $event->data->object;
                $userId = $subscription->metadata->user_id ?? null;

                if ($userId) {
                    $user = $userRepository->find($userId);
                    $freePlan = $planRepository->findOneBy(['name' => 'FREE']);

                    if ($user && $freePlan) {
                        $user->setPlan($freePlan);
                        $user->setRoles(['ROLE_USER']);
                        $em->flush();
                    }
                }
                break;

            default:
                break;
        }

        return new Response('OK', Response::HTTP_OK);
    }
}
