<?php

namespace App\Tests\Entity;

use App\Entity\UserContact;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserContactTest extends TestCase
{
    public function testGetterAndSetter(): void
    {
        $contact = new UserContact();

        $lastname = 'Dupont';
        $firstname = 'Jean';
        $email = 'jean.dupont@example.com';

        $contact->setLastname($lastname);
        $contact->setFirstname($firstname);
        $contact->setEmail($email);

        $this->assertEquals($lastname, $contact->getLastname());
        $this->assertEquals($firstname, $contact->getFirstname());
        $this->assertEquals($email, $contact->getEmail());
    }

    public function testUserRelation(): void
    {
        $contact = new UserContact();
        $user = new User();
        $user->setEmail('owner@example.com');

        $contact->setUser($user);

        $this->assertSame($user, $contact->getUser());
        $this->assertEquals('owner@example.com', $contact->getUser()->getEmail());
    }

    public function testGenerationUserContactsCollection(): void
    {
        $contact = new UserContact();

        $this->assertCount(0, $contact->getGenerationUserContacts());
    }

    public function testMultipleContacts(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $contact1 = new UserContact();
        $contact1->setFirstname('Alice');
        $contact1->setLastname('Martin');
        $contact1->setEmail('alice@example.com');
        $contact1->setUser($user);

        $contact2 = new UserContact();
        $contact2->setFirstname('Bob');
        $contact2->setLastname('Bernard');
        $contact2->setEmail('bob@example.com');
        $contact2->setUser($user);

        $this->assertEquals('Alice', $contact1->getFirstname());
        $this->assertEquals('Bob', $contact2->getFirstname());
        $this->assertSame($user, $contact1->getUser());
        $this->assertSame($user, $contact2->getUser());
    }
}
