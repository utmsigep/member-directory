<?php

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use App\Service\ContactRatingService;
use App\Entity\Member;

class ContactRatingSubscriber
{
    protected $emailService;

    public function __construct(ContactRatingService $contactRatingService)
    {
        $this->contactRatingService = $contactRatingService;
    }

    public function preUpdate(Member $member, PreUpdateEventArgs $eventArgs)
    {
        $member = $this->contactRatingService->scoreMember($member);
    }

    public function prePersist(Member $member, LifecycleEventArgs $eventArgs)
    {
        $member = $this->contactRatingService->scoreMember($member);
    }
}
