<?php

namespace App\Security\Voter;

use App\Entity\Tool;
use App\Entity\User;
use App\Repository\GenerationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter that controls access to PDF generation tools.
 *
 * Denies access if:
 *  a) The user has no plan assigned.
 *  b) The user's plan does not include the requested Tool.
 *  c) The user's daily quota (limitGeneration) has been reached.
 *     - FREE plan: 2 generations/day
 *     - BASIC plan: 10 generations/day
 *     - PREMIUM plan: unlimited (null limit)
 *
 * @extends Voter<string, Tool>
 */
class ToolAccessVoter extends Voter
{
    public const ACCESS = 'TOOL_ACCESS';

    public function __construct(private GenerationRepository $generationRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ACCESS && $subject instanceof Tool;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Tool $tool */
        $tool = $subject;

        // Check a) - User must have a plan
        $userPlan = $user->getPlan();
        if ($userPlan === null) {
            return false;
        }

        // Check b) - Plan must include the requested tool
        if (!$userPlan->getTools()->contains($tool)) {
            return false;
        }

        // Check c) - Daily quota must not be exceeded (null = unlimited)
        $limit = $userPlan->getLimitGeneration();
        if ($limit !== null) {
            $today = new \DateTime('today');
            $tomorrow = new \DateTime('tomorrow');
            $todayCount = $this->generationRepository->countPdfGeneratedByUserOnDate(
                (int) $user->getId(),
                $today,
                $tomorrow
            );

            if ($todayCount >= $limit) {
                return false;
            }
        }

        return true;
    }
}
