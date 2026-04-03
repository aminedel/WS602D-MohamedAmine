<?php

namespace App\Service;

use App\Entity\Plan;
use App\Entity\User;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct(
        private string $secretKey,
        private string $webhookSecret,
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Create a Stripe Checkout Session for a plan subscription.
     * Returns the URL to redirect the user to.
     */
    public function createCheckoutSession(
        User $user,
        Plan $plan,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $session = Session::create([
            'mode' => 'subscription',
            'customer_email' => $user->getEmail(),
            'line_items' => [[
                'price' => $plan->getStripePriceId(),
                'quantity' => 1,
            ]],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'user_id' => $user->getId(),
                'plan_id' => $plan->getId(),
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => $user->getId(),
                    'plan_id' => $plan->getId(),
                ],
            ],
        ]);

        return $session->url;
    }

    /**
     * Verify Stripe webhook signature and return the event.
     * Throws an exception if the signature is invalid.
     */
    public function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        return Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
    }
}
