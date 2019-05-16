<?php

namespace App\Service;

use USPS\Address;
use USPS\AddressVerify;

use App\Entity\Member;

class PostalValidatorService
{
    public function isConfigured(): bool
    {
        if (getenv('USPS_USERNAME')) {
            return true;
        }
        return false;
    }

    public function validate(Member $member): array
    {
        $verify = new AddressVerify(getenv('USPS_USERNAME'));
        $address = new Address();
        $address->setField('Address1', $member->getMailingAddressLine1());
        $address->setField('Address2', $member->getMailingAddressLine2());
        $address->setCity($member->getMailingCity());
        $address->setState($member->getMailingState());
        $address->setZip5($member->getMailingPostalCode());
        $address->setZip4('');
        $verify->addAddress($address);
        $verify->verify();

        return $verify->getArrayResponse();
    }
}
