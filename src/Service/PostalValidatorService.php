<?php

namespace App\Service;

use App\Entity\Member;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use USPS\Address;
use USPS\AddressVerify;

class PostalValidatorService
{
    protected $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function isConfigured(): bool
    {
        if ($this->params->get('usps.username')) {
            return true;
        }

        return false;
    }

    public function validate(Member $member): array
    {
        $verify = new AddressVerify($this->params->get('usps.username'));
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
