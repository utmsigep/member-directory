<?php

namespace App\EventListener;

use App\Entity\Donation;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

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

    public function prePersist(Donation $donation, PrePersistEventArgs $eventArgs)
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
