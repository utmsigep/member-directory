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
        'firstName',
        'preferredName',
        'middleName',
        'lastName',
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
        'employer',
        'jobTitle',
        'occupation',
        'linkedinUrl',
        'facebookUrl',
        'tags',
        'isDeceased',
        'isLost',
        'isLocalDoNotContact',
    ];

    public function arrayToCsvString(ArrayCollection $members, $columns = []): string
    {
        // Show all fields if not provided
        if (empty($columns)) {
            $columns = self::ALLOWED_COLUMNS;
        }

        $csvWriter = Writer::createFromString();
        $headers = [];
        foreach ($columns as $column) {
            if (in_array($column, self::ALLOWED_COLUMNS)) {
                $headers[] = $column;
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
