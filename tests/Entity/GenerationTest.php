<?php

namespace App\Tests\Entity;

use App\Entity\Generation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class GenerationTest extends TestCase
{
    public function testGetterAndSetter(): void
    {
        // Création d'une instance de l'entité Generation
        $generation = new Generation();

        // Définition de données de test
        $file = 'document_2026_02_04.pdf';
        $type = 'url';
        $sourceUrl = 'https://example.com';
        $createdAt = new \DateTime('2026-02-04 10:30:00');

        // Utilisation des setters
        $generation->setFile($file);
        $generation->setType($type);
        $generation->setSourceUrl($sourceUrl);
        $generation->setCreatedAt($createdAt);

        // Vérification des getters
        $this->assertEquals($file, $generation->getFile());
        $this->assertEquals($type, $generation->getType());
        $this->assertEquals($sourceUrl, $generation->getSourceUrl());
        $this->assertEquals($createdAt, $generation->getCreatedAt());
    }

    public function testCreatedAtLifecycle(): void
    {
        $generation = new Generation();

        // Le constructeur doit initialiser createdAt
        $this->assertInstanceOf(\DateTimeInterface::class, $generation->getCreatedAt());

        // Test du lifecycle callback
        $generation->setCreatedAtValue();
        $this->assertInstanceOf(\DateTimeInterface::class, $generation->getCreatedAt());
    }

    public function testUserRelation(): void
    {
        $generation = new Generation();
        $user = new User();
        $user->setEmail('test@example.com');

        $generation->setUser($user);

        $this->assertSame($user, $generation->getUser());
        $this->assertEquals('test@example.com', $generation->getUser()->getEmail());
    }

    public function testGenerationUserContactsCollection(): void
    {
        $generation = new Generation();

        // La collection doit être initialisée
        $this->assertCount(0, $generation->getGenerationUserContacts());
    }

    public function testTypeUrl(): void
    {
        $generation = new Generation();
        $generation->setType('url');
        $generation->setSourceUrl('https://symfony.com');

        $this->assertEquals('url', $generation->getType());
        $this->assertEquals('https://symfony.com', $generation->getSourceUrl());
    }

    public function testTypeFile(): void
    {
        $generation = new Generation();
        $generation->setType('file');
        $generation->setFile('uploaded_document.pdf');

        $this->assertEquals('file', $generation->getType());
        $this->assertEquals('uploaded_document.pdf', $generation->getFile());
    }

    public function testTypeWysiwyg(): void
    {
        $generation = new Generation();
        $generation->setType('wysiwyg');

        $this->assertEquals('wysiwyg', $generation->getType());
    }

    public function testFileNaming(): void
    {
        $generation = new Generation();
        $filename = 'pdf_' . date('Y_m_d_His') . '.pdf';
        $generation->setFile($filename);

        $this->assertStringContainsString('.pdf', $generation->getFile());
        $this->assertStringStartsWith('pdf_', $generation->getFile());
    }
}
