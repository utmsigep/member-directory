<?php

namespace App\Service;

use App\Entity\Member;
use Doctrine\Common\Collections\ArrayCollection;
use League\Csv\Writer;

class MemberToCsvService
{
    public const ALLOWED_COLUMNS = [
        'externalIdentifier',
        'localIdentifier',
        'prefix',
        'firstName',
        'preferredName',
        'middleName',
        'lastName',
        'suffix',
        'displayName',
        'status',
        'birthDate',
        'joinDate',
        'classYear',
        'primaryEmail',
        'primaryTelephoneNumber',
        'mailingAddressLine1',
        'mailingAddressLine2',
        'mailingCity',
        'mailingState',
        'mailingPostalCode',
        'mailingCountry',
        'mailingLatitude',
        'mailingLongitude',
        'employer',
        'jobTitle',
        'occupation',
        'linkedinUrl',
        'facebookUrl',
        'tags',
        'isDeceased',
        'isLost',
        'isLocalDoNotContact',
        'updatedAt',
    ];

    public function arrayToCsvString(ArrayCollection $members, $columns = []): string
    {
        // Show all fields if not provided
        if (empty($columns)) {
            $columns = self::ALLOWED_COLUMNS;
        }

        $csvWriter = Writer::createFromString();
        $headers = [];
        foreach (self::ALLOWED_COLUMNS as $allowedColumn) {
            if (in_array($allowedColumn, $columns)) {
                $headers[] = $allowedColumn;
            }
        }
        $csvWriter->insertOne($headers);
        foreach ($members as $member) {
            $csvWriter->insertOne($this->memberToArray($member, $headers));
        }

        return $csvWriter->getContent();
    }

    private function memberToArray(Member $member, $columns = [])
    {
        $row = [];
        foreach ($columns as $column) {
            $methodName = sprintf('get%s', ucfirst($column));
            if (is_callable([$member, $methodName])) {
                switch ($column) {
                    case 'birthDate':
                        $row[] = ($member->getBirthDate()) ? $member->getBirthDate()->format('Y-m-d') : '';
                        break;
                    case 'joinDate':
                        $row[] = ($member->getJoinDate()) ? $member->getJoinDate()->format('Y-m-d') : '';
                        break;
                    case 'updatedAt':
                        $row[] = $member->getUpdatedAt()->format('Y-m-d h:i:s');
                        break;
                    case 'tags':
                        $row[] = $member->getTagsAsCSV();
                        break;
                    default:
                        $row[] = (string) $member->$methodName();
                }
            } else {
                $row[] = '#ERROR';
            }
        }

        return $row;
    }
}
