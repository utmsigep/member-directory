<?php

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use App\Entity\Donation;

class DonationSubscriber
{
    public function preUpdate(Donation $donation, PreUpdateEventArgs $eventArgs)
    {
        if ($donation->getMember()) {
            $donation->setDonorFirstName($donation->getMember()->getFirstName());
            $donation->setDonorLastName($donation->getMember()->getLastName());
        }
        if (!$donation->getMember() && !$donation->getDonorFirstName() && !$donation->getDonorLastName()) {
            $donation->setIsAnonymous(true);
        }
    }

    public function prePersist(Donation $donation, LifecycleEventArgs $eventArgs)
    {
        if ($donation->getMember()) {
            $donation->setDonorFirstName($donation->getMember()->getFirstName());
            $donation->setDonorLastName($donation->getMember()->getLastName());
        }
        if (!$donation->getMember() && !$donation->getDonorFirstName() && !$donation->getDonorLastName()) {
            $donation->setIsAnonymous(true);
        }
    }
}
