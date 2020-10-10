<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use League\Csv\Writer;

use App\Entity\Member;

class MemberToCsvService
{
    public function arrayToCsvString(ArrayCollection $members): string
    {
        $csvWriter = Writer::createFromString();
        $csvWriter->insertOne([
            'externalIdentifier',
            'localIdentifier',
            'status',
            'firstName',
            'preferredName',
            'middleName',
            'lastName',
            'classYear',
            'primaryEmail',
            'primaryTelephoneNumber',
            'mailingAddressLine1',
            'mailingAddressLine2',
            'mailingCity',
            'mailingState',
            'mailingPostalCode',
            'mailingCountry',
            'employer',
            'jobTitle',
            'occupation',
            'linkedinUrl',
            'facebookUrl',
            'tags',
            'isDeceased',
            'isLost',
            'isLocalDoNotContact'
        ]);
        foreach ($members as $member) {
            $csvWriter->insertOne($this->memberToArray($member));
        }
        return $csvWriter->getContent();
    }

    private function memberToArray(Member $member)
    {
        return [
            $member->getExternalIdentifier(),
            $member->getLocalIdentifier(),
            $member->getStatus(),
            $member->getFirstName(),
            $member->getPreferredName(),
            $member->getMiddleName(),
            $member->getLastName(),
            $member->getClassYear(),
            $member->getPrimaryEmail(),
            $member->getPrimaryTelephoneNumber(),
            $member->getMailingAddressLine1(),
            $member->getMailingAddressLine2(),
            $member->getMailingCity(),
            $member->getMailingState(),
            $member->getMailingPostalCode(),
            $member->getMailingCountry(),
            $member->getEmployer(),
            $member->getJobTitle(),
            $member->getOccupation(),
            $member->getLinkedinUrl(),
            $member->getFacebookUrl(),
            $member->getTagsAsCSV(),
            $member->getIsDeceased(),
            $member->getIsLost(),
            $member->getIsLocalDoNotContact()
        ];
    }
}
