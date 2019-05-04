<?php

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use App\Service\GeocoderService;
use App\Entity\Member;

class GeocodeMemberSubscriber
{
    protected $geocoderService;

    public function __construct(GeocoderService $geocoderService)
    {
        $this->geocoderService = $geocoderService;
    }

    public function preUpdate(Member $member, PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->hasChangedField('mailingAddressLine1')
            || $eventArgs->hasChangedField('mailingAddressLine2')
            || $eventArgs->hasChangedField('mailingCity')
            || $eventArgs->hasChangedField('mailingState')
            || $eventArgs->hasChangedField('mailingPostalCode')
            || $eventArgs->hasChangedField('mailingCountry')
        ) {
            // Clear existing coordinates on save
            $member->setMailingLatitude(null);
            $member->setMailingLongitude(null);
            $this->geocoderService->geocodeMemberMailingAddress($member);
        }
    }

    public function prePersist(Member $member, LifecycleEventArgs $eventArgs)
    {
        if ($member->getMailingAddressLine1()
            || $member->getMailingAddressLine2()
            || $member->getMailingCity()
            || $member->getMailingState()
            || $member->getMailingPostalCode()
            || $member->getMailingcountry()
        ) {
            // Do not call service if the created record includes coordinates
            if (!$member->getMailingLatitude() && !$member->getMailingLongitude()) {
                $this->geocoderService->geocodeMemberMailingAddress($member);
            }
        }
    }
}
