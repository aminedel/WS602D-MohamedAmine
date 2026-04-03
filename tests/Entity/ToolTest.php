<?php

namespace App\Tests\Entity;

use App\Entity\Plan;
use App\Entity\Tool;
use PHPUnit\Framework\TestCase;

class ToolTest extends TestCase
{
    public function testGetterAndSetter(): void
    {
        $tool = new Tool();

        $tool->setName('URL vers PDF');
        $tool->setSlug('url');

        $this->assertEquals('URL vers PDF', $tool->getName());
        $this->assertEquals('url', $tool->getSlug());
        $this->assertNull($tool->getId());
    }

    public function testPlansCollection(): void
    {
        $tool = new Tool();
        $this->assertCount(0, $tool->getPlans());

        $plan = new Plan();
        $plan->setName('FREE');
        $plan->addTool($tool);

        // addPlan is called via plan->addTool
        $this->assertCount(1, $tool->getPlans());
        $this->assertTrue($tool->getPlans()->contains($plan));
    }

    public function testRemovePlan(): void
    {
        $tool = new Tool();
        $plan = new Plan();
        $plan->setName('BASIC');

        $tool->addPlan($plan);
        $this->assertCount(1, $tool->getPlans());

        $tool->removePlan($plan);
        $this->assertCount(0, $tool->getPlans());
    }
}
