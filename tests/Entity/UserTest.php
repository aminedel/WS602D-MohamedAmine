<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Plan;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetterAndSetter(): void
    {
        // Création d'une instance de l'entité User
        $user = new User();

        // Définition de données de test
        $email = 'test@example.com';
        $password = 'hashedPassword123';
        $lastname = 'Delhoum';
        $firstname = 'Mohamed Amine';
        $dob = new \DateTime('1995-05-15');
        $photo = 'profile.jpg';
        $favoriteColor = 'blue';
        $phone = '+33612345678';

        // Utilisation des setters
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setLastname($lastname);
        $user->setFirstname($firstname);
        $user->setDob($dob);
        $user->setPhoto($photo);
        $user->setFavoriteColor($favoriteColor);
        $user->setPhone($phone);

        // Vérification des getters
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals($lastname, $user->getLastname());
        $this->assertEquals($firstname, $user->getFirstname());
        $this->assertEquals($dob, $user->getDob());
        $this->assertEquals($photo, $user->getPhoto());
        $this->assertEquals($favoriteColor, $user->getFavoriteColor());
        $this->assertEquals($phone, $user->getPhone());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $email = 'user@test.com';
        $user->setEmail($email);

        $this->assertEquals($email, $user->getUserIdentifier());
    }

    public function testRoles(): void
    {
        $user = new User();

        // Par défaut, un utilisateur doit avoir ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles());

        // Test avec des rôles personnalisés
        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testPlanRelation(): void
    {
        $user = new User();
        $plan = new Plan();
        $plan->setName('PREMIUM');

        $user->setPlan($plan);

        $this->assertSame($plan, $user->getPlan());
        $this->assertEquals('PREMIUM', $user->getPlan()->getName());
    }

    public function testCreatedAtLifecycle(): void
    {
        $user = new User();

        // Le constructeur doit initialiser createdAt
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt());

        // Test du lifecycle callback
        $user->setCreatedAtValue();
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt());
    }

    public function testIsVerified(): void
    {
        $user = new User();

        // Par défaut, l'utilisateur n'est pas vérifié
        $this->assertFalse($user->isVerified());

        $user->setVerified(true);
        $this->assertTrue($user->isVerified());
    }

    public function testGenerationsCollection(): void
    {
        $user = new User();

        // La collection doit être initialisée
        $this->assertCount(0, $user->getGenerations());
    }

    public function testUserContactsCollection(): void
    {
        $user = new User();

        // La collection doit être initialisée
        $this->assertCount(0, $user->getUserContacts());
    }

    public function testStripeCustomerId(): void
    {
        $user = new User();

        $this->assertNull($user->getStripeCustomerId());

        $user->setStripeCustomerId('cus_test123');
        $this->assertEquals('cus_test123', $user->getStripeCustomerId());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->eraseCredentials();
        // Should not throw
        $this->assertTrue(true);
    }
}
