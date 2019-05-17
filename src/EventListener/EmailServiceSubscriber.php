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
        if (!$this->emailService->isConfigured()) {
            return;
        }
        // If set to 'Do Not Contact (Local)', unsubscribe the user
        if ($eventArgs->hasChangedField('isLocalDoNotContact') && $member->getIsLocalDoNotContact()) {
            $this->emailService->unsubscribeMember($member);
            return;
        }
        // If status moved to Resigned/Expelled, delete the user user's subscription
        if ($eventArgs->hasChangedField('status') && in_array($member->getStatus()->getCode(), [
            'RESIGNED',
            'EXPELLED',
            'TRANSFERRED'
        ])) {
            $this->emailService->deleteMember($member);
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
            if ($member->getPrimaryEmail()) {
                $this->emailService->updateMember(
                    $member->getPrimaryEmail(),
                    $member
                );
            }
        }
    }

    public function prePersist(Member $member, LifecycleEventArgs $eventArgs)
    {
        if (!$this->emailService->isConfigured()
            || in_array($member->getStatus()->getCode(), [
                'RESIGNED',
                'EXPELLED',
                'TRANSFERRED'
            ])
        ) {
            return;
        }
        // Auto subscribe added members
        if ($member->getPrimaryEmail()) {
            $this->emailService->subscribeMember($member);
        }
    }
}
