<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PlanFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function getDependencies(): array
    {
        return [AppFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // Retrieve plans created by AppFixtures
        $freePlan = $manager->getRepository(Plan::class)->findOneBy(['name' => 'FREE']);
        // $basicPlan is not used here, only FREE and PREMIUM
        $premiumPlan = $manager->getRepository(Plan::class)->findOneBy(['name' => 'PREMIUM']);

        // =============================================
        // TEST USER (for Cypress tests and evaluation)
        // =============================================

        $testUser = new User();
        $testUser->setEmail('test@example.com');
        $testUser->setFirstname('Mohamed');
        $testUser->setLastname('Amine');
        $testUser->setPhone('+33612345678');
        $testUser->setPlan($freePlan);
        $testUser->setVerified(true);
        $testUser->setPassword(
            $this->passwordHasher->hashPassword($testUser, 'password123')
        );
        $manager->persist($testUser);

        // Admin user
        $adminUser = new User();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setFirstname('Admin');
        $adminUser->setLastname('System');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setPlan($premiumPlan);
        $adminUser->setVerified(true);
        $adminUser->setPassword(
            $this->passwordHasher->hashPassword($adminUser, 'admin123')
        );
        $manager->persist($adminUser);

        $manager->flush();
    }
}
