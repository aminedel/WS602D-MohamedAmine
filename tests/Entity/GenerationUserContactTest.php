<?php

namespace App\Tests\Entity;

use App\Entity\GenerationUserContact;
use App\Entity\Generation;
use App\Entity\UserContact;
use PHPUnit\Framework\TestCase;

class GenerationUserContactTest extends TestCase
{
    public function testGetterAndSetter(): void
    {
        $genContact = new GenerationUserContact();

        $generation = new Generation();
        $generation->setFile('test.pdf');
        $generation->setType('url');

        $userContact = new UserContact();
        $userContact->setFirstname('Jean');
        $userContact->setLastname('Dupont');
        $userContact->setEmail('jean@example.com');

        $genContact->setGeneration($generation);
        $genContact->setUserContact($userContact);

        $this->assertSame($generation, $genContact->getGeneration());
        $this->assertSame($userContact, $genContact->getUserContact());
    }

    public function testGenerationRelation(): void
    {
        $genContact = new GenerationUserContact();
        $generation = new Generation();
        $generation->setFile('document.pdf');

        $genContact->setGeneration($generation);

        $this->assertSame($generation, $genContact->getGeneration());
        $this->assertEquals('document.pdf', $genContact->getGeneration()->getFile());
    }

    public function testUserContactRelation(): void
    {
        $genContact = new GenerationUserContact();
        $userContact = new UserContact();
        $userContact->setEmail('contact@example.com');

        $genContact->setUserContact($userContact);

        $this->assertSame($userContact, $genContact->getUserContact());
        $this->assertEquals('contact@example.com', $genContact->getUserContact()->getEmail());
    }
}
