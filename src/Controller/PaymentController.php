<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Repository\PlanRepository;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/checkout/{id}', name: 'app_payment_checkout')]
    public function checkout(
        Plan $plan,
        StripeService $stripeService,
    ): Response {
        if ($plan->getStripePriceId() === null) {
            $this->addFlash('info', 'Ce plan est gratuit, aucun paiement requis.');
            return $this->redirectToRoute('app_home');
        }

        $successUrl = $this->generateUrl(
            'app_payment_success',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $cancelUrl = $this->generateUrl(
            'app_payment_cancel',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $checkoutUrl = $stripeService->createCheckoutSession(
            $user,
            $plan,
            $successUrl,
            $cancelUrl,
        );

        return $this->redirect($checkoutUrl);
    }

    #[Route('/success', name: 'app_payment_success')]
    public function success(): Response
    {
        return $this->render('payment/success.html.twig');
    }

    #[Route('/cancel', name: 'app_payment_cancel')]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}
