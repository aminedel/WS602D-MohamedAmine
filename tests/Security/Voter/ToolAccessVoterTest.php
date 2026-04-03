<?php

namespace App\Tests\Security\Voter;

use App\Entity\Plan;
use App\Entity\Tool;
use App\Entity\User;
use App\Repository\GenerationRepository;
use App\Security\Voter\ToolAccessVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ToolAccessVoterTest extends TestCase
{
    public function testVoteOnAttributeGrantsAccess(): void
    {
        $generationRepo = $this->createMock(GenerationRepository::class);
        $generationRepo->method('countPdfGeneratedByUserOnDate')->willReturn(1);

        $voter = new ToolAccessVoter($generationRepo);

        $tool = new Tool();
        $tool->setName('HTML');

        $plan = new Plan();
        $plan->setName('BASIC');
        $plan->setLimitGeneration(5);
        $plan->addTool($tool);

        $user = new User();
        $user->setPlan($plan);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $result = $voter->vote($token, $tool, ['TOOL_ACCESS']);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testVoteOnAttributeDeniesAccessLimitReached(): void
    {
        $generationRepo = $this->createMock(GenerationRepository::class);
        $generationRepo->method('countPdfGeneratedByUserOnDate')->willReturn(5); // Limite atteinte

        $voter = new ToolAccessVoter($generationRepo);

        $tool = new Tool();
        $tool->setName('HTML');

        $plan = new Plan();
        $plan->setName('BASIC');
        $plan->setLimitGeneration(5);
        $plan->addTool($tool);

        $user = new User();
        $user->setPlan($plan);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $result = $voter->vote($token, $tool, ['TOOL_ACCESS']);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }
}
