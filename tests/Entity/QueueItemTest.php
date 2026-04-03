<?php

namespace App\Tests\Entity;

use App\Entity\QueueItem;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class QueueItemTest extends TestCase
{
    public function testGetterAndSetter(): void
    {
        $item = new QueueItem();

        $item->setType('merge');
        $item->setPayload(['files' => ['a.pdf', 'b.pdf']]);
        $item->setStatus(QueueItem::STATUS_PENDING);
        $item->setResultFile('merged.pdf');
        $item->setErrorMessage(null);

        $this->assertEquals('merge', $item->getType());
        $this->assertEquals(['files' => ['a.pdf', 'b.pdf']], $item->getPayload());
        $this->assertEquals(QueueItem::STATUS_PENDING, $item->getStatus());
        $this->assertEquals('merged.pdf', $item->getResultFile());
        $this->assertNull($item->getErrorMessage());
        $this->assertNull($item->getId());
        $this->assertInstanceOf(\DateTimeInterface::class, $item->getCreatedAt());
    }

    public function testUserRelation(): void
    {
        $item = new QueueItem();
        $user = new User();
        $user->setEmail('test@example.com');

        $item->setUser($user);
        $this->assertSame($user, $item->getUser());
    }

    public function testStatusTransitions(): void
    {
        $item = new QueueItem();

        $this->assertEquals(QueueItem::STATUS_PENDING, $item->getStatus());

        $item->setStatus(QueueItem::STATUS_PROCESSING);
        $this->assertEquals(QueueItem::STATUS_PROCESSING, $item->getStatus());

        $item->setStatus(QueueItem::STATUS_DONE);
        $item->setProcessedAt(new \DateTime());
        $this->assertEquals(QueueItem::STATUS_DONE, $item->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $item->getProcessedAt());
    }

    public function testErrorStatus(): void
    {
        $item = new QueueItem();

        $item->setStatus(QueueItem::STATUS_ERROR);
        $item->setErrorMessage('Gotenberg unavailable');

        $this->assertEquals(QueueItem::STATUS_ERROR, $item->getStatus());
        $this->assertEquals('Gotenberg unavailable', $item->getErrorMessage());
    }

    public function testConstants(): void
    {
        $this->assertEquals('pending', QueueItem::STATUS_PENDING);
        $this->assertEquals('processing', QueueItem::STATUS_PROCESSING);
        $this->assertEquals('done', QueueItem::STATUS_DONE);
        $this->assertEquals('error', QueueItem::STATUS_ERROR);
    }
}
