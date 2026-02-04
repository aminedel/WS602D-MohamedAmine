<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlanFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // FREE Plan
        $freePlan = new Plan();
        $freePlan->setName('FREE');
        $freePlan->setDescription('Plan gratuit pour découvrir notre service de génération de PDF. Idéal pour tester les fonctionnalités de base.');
        $freePlan->setLimitGeneration(2); // 2 PDFs par jour
        $freePlan->setRole('ROLE_USER');
        $freePlan->setPrice('0.00');
        $freePlan->setActive(true);
        $freePlan->setImage('free-plan.png');
        $manager->persist($freePlan);

        // BASIC Plan
        $basicPlan = new Plan();
        $basicPlan->setName('BASIC');
        $basicPlan->setDescription('Plan basique pour les utilisateurs réguliers. Parfait pour un usage quotidien avec plus de flexibilité.');
        $basicPlan->setLimitGeneration(50); // 50 PDFs par jour
        $basicPlan->setRole('ROLE_USER');
        $basicPlan->setPrice('9.99');
        $basicPlan->setSpecialPrice('7.99');
        $basicPlan->setSpecialPriceFrom(new \DateTime('2026-02-01'));
        $basicPlan->setSpecialPriceTo(new \DateTime('2026-03-31'));
        $basicPlan->setActive(true);
        $basicPlan->setImage('basic-plan.png');
        $manager->persist($basicPlan);

        // PREMIUM Plan
        $premiumPlan = new Plan();
        $premiumPlan->setName('PREMIUM');
        $premiumPlan->setDescription('Plan premium pour les professionnels. Génération illimitée de PDF avec toutes les fonctionnalités avancées.');
        $premiumPlan->setLimitGeneration(null); // Illimité
        $premiumPlan->setRole('ROLE_PREMIUM');
        $premiumPlan->setPrice('29.99');
        $premiumPlan->setSpecialPrice('24.99');
        $premiumPlan->setSpecialPriceFrom(new \DateTime('2026-02-01'));
        $premiumPlan->setSpecialPriceTo(new \DateTime('2026-03-31'));
        $premiumPlan->setActive(true);
        $premiumPlan->setImage('premium-plan.png');
        $manager->persist($premiumPlan);

        $manager->flush();
    }
}
