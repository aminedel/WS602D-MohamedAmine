<?php

namespace App\Tests\Entity;

use App\Entity\Plan;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    public function testGetterAndSetter(): void
    {
        // Création d'une instance de l'entité Plan
        $plan = new Plan();

        // Définition de données de test
        $name = 'PREMIUM';
        $description = 'Plan premium avec génération illimitée';
        $limitGeneration = null; // Illimité
        $image = 'premium.png';
        $role = 'ROLE_PREMIUM';
        $price = '29.99';
        $specialPrice = '24.99';
        $specialPriceFrom = new \DateTime('2026-02-01');
        $specialPriceTo = new \DateTime('2026-03-31');
        $active = true;

        // Utilisation des setters
        $plan->setName($name);
        $plan->setDescription($description);
        $plan->setLimitGeneration($limitGeneration);
        $plan->setImage($image);
        $plan->setRole($role);
        $plan->setPrice($price);
        $plan->setSpecialPrice($specialPrice);
        $plan->setSpecialPriceFrom($specialPriceFrom);
        $plan->setSpecialPriceTo($specialPriceTo);
        $plan->setActive($active);

        // Vérification des getters
        $this->assertEquals($name, $plan->getName());
        $this->assertEquals($description, $plan->getDescription());
        $this->assertEquals($limitGeneration, $plan->getLimitGeneration());
        $this->assertEquals($image, $plan->getImage());
        $this->assertEquals($role, $plan->getRole());
        $this->assertEquals($price, $plan->getPrice());
        $this->assertEquals($specialPrice, $plan->getSpecialPrice());
        $this->assertEquals($specialPriceFrom, $plan->getSpecialPriceFrom());
        $this->assertEquals($specialPriceTo, $plan->getSpecialPriceTo());
        $this->assertTrue($plan->isActive());
    }

    public function testCreatedAtLifecycle(): void
    {
        $plan = new Plan();

        // Le constructeur doit initialiser createdAt
        $this->assertInstanceOf(\DateTimeInterface::class, $plan->getCreatedAt());

        // Test du lifecycle callback
        $plan->setCreatedAtValue();
        $this->assertInstanceOf(\DateTimeInterface::class, $plan->getCreatedAt());
    }

    public function testDefaultActiveValue(): void
    {
        $plan = new Plan();

        // Par défaut, un plan doit être actif
        $this->assertTrue($plan->isActive());
    }

    public function testGetCurrentPriceWithSpecialPrice(): void
    {
        $plan = new Plan();
        $plan->setPrice('29.99');
        $plan->setSpecialPrice('24.99');
        $plan->setSpecialPriceFrom(new \DateTime('-1 day'));
        $plan->setSpecialPriceTo(new \DateTime('+1 day'));

        // Le prix actuel doit être le prix spécial car nous sommes dans la période
        $this->assertEquals('24.99', $plan->getCurrentPrice());
    }

    public function testGetCurrentPriceWithoutSpecialPrice(): void
    {
        $plan = new Plan();
        $plan->setPrice('29.99');

        // Le prix actuel doit être le prix normal
        $this->assertEquals('29.99', $plan->getCurrentPrice());
    }

    public function testGetCurrentPriceWithExpiredSpecialPrice(): void
    {
        $plan = new Plan();
        $plan->setPrice('29.99');
        $plan->setSpecialPrice('24.99');
        $plan->setSpecialPriceFrom(new \DateTime('-10 days'));
        $plan->setSpecialPriceTo(new \DateTime('-1 day'));

        // Le prix actuel doit être le prix normal car la période est expirée
        $this->assertEquals('29.99', $plan->getCurrentPrice());
    }

    public function testUsersCollection(): void
    {
        $plan = new Plan();

        // La collection doit être initialisée
        $this->assertCount(0, $plan->getUsers());
    }

    public function testAddUser(): void
    {
        $plan = new Plan();
        $user = new User();

        $plan->addUser($user);

        $this->assertCount(1, $plan->getUsers());
        $this->assertTrue($plan->getUsers()->contains($user));
    }

    public function testRemoveUser(): void
    {
        $plan = new Plan();
        $user = new User();

        $plan->addUser($user);
        $this->assertCount(1, $plan->getUsers());

        $plan->removeUser($user);
        $this->assertCount(0, $plan->getUsers());
    }

    public function testFreePlan(): void
    {
        $plan = new Plan();
        $plan->setName('FREE');
        $plan->setLimitGeneration(2);
        $plan->setPrice('0.00');

        $this->assertEquals('FREE', $plan->getName());
        $this->assertEquals(2, $plan->getLimitGeneration());
        $this->assertEquals('0.00', $plan->getPrice());
    }

    public function testPremiumPlanUnlimited(): void
    {
        $plan = new Plan();
        $plan->setName('PREMIUM');
        $plan->setLimitGeneration(null); // Illimité

        $this->assertEquals('PREMIUM', $plan->getName());
        $this->assertNull($plan->getLimitGeneration());
    }
}
