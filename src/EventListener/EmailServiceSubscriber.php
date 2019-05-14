<?php

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use App\Service\EmailService;
use App\Entity\Member;

class EmailServiceSubscriber
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function preUpdate(Member $member, PreUpdateEventArgs $eventArgs)
    {
        // If set to 'Do Not Contact (Local)', unsubscribe the user
        if ($eventArgs->hasChangedField('isLocalDoNotContact') && $member->isLocalDoNotContact()) {
            $this->emailService->unsubscribeMember($member);
            return;
        }
        // If an email address was set and has been changed, update email address in ESP
        if ($eventArgs->hasChangedField('primaryEmail')
            && $eventArgs->getOldValue('primaryEmail')
            && $eventArgs->getNewValue('primaryEmail')
        ) {
            $this->emailService->updateMember(
                $eventArgs->getOldValue('primaryEmail'),
                $member
            );
        } else {
            $this->emailService->updateMember(
                $member->getPrimaryEmail(),
                $member
            );
        }
    }

    public function prePersist(Member $member, LifecycleEventArgs $eventArgs)
    {
        // Auto subscribe added members
        $this->emailService->subscribeMember($member);
    }
}
