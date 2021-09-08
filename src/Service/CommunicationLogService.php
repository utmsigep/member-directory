<?php

namespace App\Service;

use App\Entity\CommunicationLog;
use App\Entity\Member;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CommunicationLogService
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function log(string $type, string $summary, Member $member, ?User $actor, array $payload = []): void
    {
        if (!isset(CommunicationLog::COMMUNICATION_TYPES[$type])) {
            throw new \Exception(sprintf('Invalid log type: %s', $type));
        }
        $communicationLog = new CommunicationLog();
        $communicationLog->setMember($member);
        $communicationLog->setUser($actor);
        $communicationLog->setType(CommunicationLog::COMMUNICATION_TYPES[$type]);
        $communicationLog->setSummary($summary);
        $communicationLog->setPayload($payload);
        $this->entityManager->persist($communicationLog);
        $this->entityManager->flush();
    }
}
