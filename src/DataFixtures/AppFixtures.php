<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use App\Entity\Tool;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // =============================================
        // TOOLS
        // =============================================
        $toolsData = [
            ['name' => 'URL vers PDF', 'slug' => 'url'],
            ['name' => 'Fusion PDF', 'slug' => 'merge'],
            ['name' => 'HTML vers PDF', 'slug' => 'html'],
            ['name' => 'Markdown vers PDF', 'slug' => 'markdown'],
            ['name' => 'Office vers PDF', 'slug' => 'office'],
            ['name' => 'Capture d\'écran vers PNG', 'slug' => 'screenshot'],
            ['name' => 'WYSIWYG vers PDF', 'slug' => 'wysiwyg'],
        ];

        $tools = [];
        foreach ($toolsData as $data) {
            $tool = new Tool();
            $tool->setName($data['name']);
            $tool->setSlug($data['slug']);
            $manager->persist($tool);
            $tools[$data['slug']] = $tool;
        }

        // =============================================
        // PLANS (FREE=0, BASIC=9.90, PREMIUM=45)
        // =============================================
        $plansData = [
            [
                'name' => 'FREE',
                'description' => 'Plan gratuit pour découvrir notre service. Accès aux outils de base avec 2 générations par jour.',
                'price' => '0.00',
                'limit' => 2,
                'role' => 'ROLE_USER',
                'tools' => ['url', 'merge'],
                'stripePriceId' => null,
            ],
            [
                'name' => 'BASIC',
                'description' => 'Pour les utilisateurs réguliers. Accès à HTML, Markdown et Office avec 10 générations par jour.',
                'price' => '9.90',
                'limit' => 10,
                'role' => 'ROLE_USER',
                'tools' => ['url', 'merge', 'html', 'markdown', 'office'],
                'stripePriceId' => 'price_basic_placeholder',
            ],
            [
                'name' => 'PREMIUM',
                'description' => 'Accès illimité à tous les outils. Génération illimitée avec support prioritaire.',
                'price' => '45.00',
                'limit' => null,
                'role' => 'ROLE_PREMIUM',
                'tools' => ['url', 'merge', 'html', 'markdown', 'office', 'screenshot', 'wysiwyg'],
                'stripePriceId' => 'price_premium_placeholder',
            ],
        ];

        foreach ($plansData as $data) {
            $plan = new Plan();
            $plan->setName($data['name']);
            $plan->setDescription($data['description']);
            $plan->setPrice($data['price']);
            $plan->setLimitGeneration($data['limit']);
            $plan->setRole($data['role']);
            $plan->setActive(true);
            $plan->setStripePriceId($data['stripePriceId']);
            foreach ($data['tools'] as $toolSlug) {
                $plan->addTool($tools[$toolSlug]);
            }
            $manager->persist($plan);
        }

        $manager->flush();
    }
}
