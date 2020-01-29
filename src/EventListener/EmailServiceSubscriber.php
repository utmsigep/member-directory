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
            'EXPELLED'
        ])) {
            $this->emailService->deleteMember($member);
            return;
        }
        // If member is now deceased, unsubscribe
        if ($eventArgs->hasChangedField('isDeceased') && $member->isDeceased()) {
            $this->emailService->unsubscribeMember($member);
            return;
        }
        // If email was previously set and has been changed, update email address in ESP
        if ($eventArgs->hasChangedField('primaryEmail')
            && $eventArgs->getOldValue('primaryEmail')
            && $eventArgs->getNewValue('primaryEmail')
        ) {
            $this->emailService->updateMember(
                $eventArgs->getOldValue('primaryEmail'),
                $member
            );
            // Re-subscribe user if old address was on supression list
            $this->emailService->subscribeMember($member, true);
            return;
        }
        // If email was removed from record, delete previous record in ESP
        if ($eventArgs->hasChangedField('primaryEmail')
            && $eventArgs->getOldValue('primaryEmail')
            && !$eventArgs->getNewValue('primaryEmail')
        ) {
            $this->emailService->deleteMember($member);
        }
        // If email added to a record, subscribe in ESP
        if ($eventArgs->hasChangedField('primaryEmail')
            && !$eventArgs->getOldValue('primaryEmail')
            && $eventArgs->getNewValue('primaryEmail')
        ) {
            $this->emailService->subscribeMember($member);
            return;
        }
        // Update Member Record in ESP, if email exists
        if ($member->getPrimaryEmail()) {
            $this->emailService->updateMember(
                $member->getPrimaryEmail(),
                $member
            );
        }
    }

    public function prePersist(Member $member, LifecycleEventArgs $eventArgs)
    {
        if (!$this->emailService->isConfigured()
            || in_array($member->getStatus()->getCode(), [
                'RESIGNED',
                'EXPELLED'
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
