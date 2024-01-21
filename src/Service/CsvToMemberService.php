<?php

namespace App\Service;

use App\Entity\Member;
use App\Entity\MemberStatus;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader as CsvReader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CsvToMemberService
{
    public const COLUMN_MAPPINGS = [
        'localIdentifier' => [
            'Local Identifier',
            'Local ID',
            'Chapter Number',
            'Chapter ID',
        ],
        'externalIdentifier' => [
            'External Identifier',
            'External ID',
            'National Number',
            'National ID',
        ],
        'prefix' => [
            'Prefix',
            'Salutation',
        ],
        'firstName' => [
            'First Name',
            'First',
        ],
        'middleName' => [
            'Middle Name',
            'Middle',
            'Middle Initial',
            'Middle Initials',
            'Middlename',
        ],
        'preferredName' => [
            'Preferred Name',
            'Preferred',
            'Nickname',
            'Nick',
        ],
        'lastName' => [
            'Last Name',
            'Last',
            'Surname',
        ],
        'suffix' => [
            'Suffix',
        ],
        'status' => [
            'Status',
            'Status Code',
            'Member Status',
            'Member Status Code',
        ],
        'birthDate' => [
            'Birth Date',
            'Birthday',
            'Birth',
            'Birthday Date',
            'Date of Birth',
        ],
        'joinDate' => [
            'Join Date',
            'Join',
            'Joined',
        ],
        'classYear' => [
            'Class Year',
            'Class',
            'Graduation Year',
            'Year',
        ],
        'isDeceased' => [
            'Is Deceased',
            'Is Deceased?',
            'Deceased',
            'Deceased?',
        ],
        'employer' => [
            'Employer',
            'Employer Name',
        ],
        'jobTitle' => [
            'Job Title',
            'Job',
            'Title',
        ],
        'occupation' => [
            'Occupation',
            'Occupation Name',
            'Industry',
        ],
        'primaryEmail' => [
            'Primary Email',
            'Email',
            'Email Address',
            'E-mail',
            'E-mail Address',
        ],
        'primaryTelephoneNumber' => [
            'Primary Telephone Number',
            'Telephone',
            'Telephone Number',
            'Phone',
            'Phone Number',
            'Mobile',
            'Mobile Number',
            'Cell',
            'Cell Number',
        ],
        'mailingAddress' => [
            'Mailing Address',
            'Street Address',
            'Address',
        ],
        'mailingAddressLine1' => [
            'Mailing Address Line 1',
            'Address Line 1',
            'Address 1',
            'Line 1',
        ],
        'mailingAddressLine2' => [
            'Mailing Address Line 2',
            'Address Line 2',
            'Address 2',
            'Line 2',
        ],
        'mailingCity' => [
            'Mailing City',
            'City',
        ],
        'mailingState' => [
            'Mailing State',
            'State',
            'Province',
            'Province/State',
            'State/Province',
        ],
        'mailingPostalCode' => [
            'Mailing Postal Code',
            'Postal Code',
            'Postal',
            'Zip',
            'Zip Code',
            'Zip/Postal Code',
            'Postal Code/Zip',
            'Postal/Zip Code',
        ],
        'mailingCountry' => [
            'Mailing Country',
            'Country',
            'Country/Region',
        ],
        'mailingLatitude' => [
            'Mailing Latitude',
            'Latitude',
            'Lat',
        ],
        'mailingLongitude' => [
            'Mailing Longitude',
            'Longitude',
            'Long',
            'Lng',
        ],
        'isLost' => [
            'Is Lost',
            'Lost',
            'Lost?',
        ],
        'isLocalDoNotContact' => [
            'Is Do Not Contact',
            'Do Not Contact',
            'Do Not Contact?',
        ],
        'directoryNotes' => [
            'Directory Notes',
            'Notes',
            'Note',
        ],
    ];

    protected $entityManager;

    protected $validator;

    protected $memberStatusMap;

    protected $members = [];

    protected $errors = [];

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->loadMemberStatusMapping();
    }

    public function getMembers()
    {
        return $this->members;
    }

    public function getErrors()
    {
        $consolidated = [];
        foreach ($this->errors as $i => $rowErrors) {
            $rowNumber = $i + 1;
            $consolidated[$i] = "Row {$rowNumber}: ".join('; ', $rowErrors);
        }

        return $consolidated;
    }

    public function run(UploadedFile $file)
    {
        // Ensure a file is selected
        if (!$file->isValid()) {
            throw new \Exception('Uploaded file is invalid.');
        }

        // Parse loaded file
        $csv = CsvReader::createFromPath($file->getPath().DIRECTORY_SEPARATOR.$file->getFileName(), 'r');
        $csv->setHeaderOffset(0);

        // Main import loop
        foreach ($csv->getRecords() as $i => $csvRecord) {
            $rowNumber = $i + 1;
            $externalIdentifier = $this->findMappedValue('externalIdentifier', $csvRecord);
            $localIdentifier = $this->findMappedValue('localIdentifier', $csvRecord);
            $firstName = $this->findMappedValue('firstName', $csvRecord);
            $lastName = $this->findMappedValue('lastName', $csvRecord);

            $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                'externalIdentifier' => $externalIdentifier,
            ]);

            if (null === $member) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'localIdentifier' => $localIdentifier,
                ]);
            }

            if (null === $member) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                ]);
            }

            if (null === $member) {
                $member = new Member();
            }

            // Populate fields if set
            if (null !== $this->findMappedValue('externalIdentifier', $csvRecord)) {
                $member->setExternalIdentifier($this->findMappedValue('externalIdentifier', $csvRecord));
            }
            if (null !== $this->findMappedValue('localIdentifier', $csvRecord)) {
                $member->setLocalIdentifier($this->findMappedValue('localIdentifier', $csvRecord));
            }
            if (null !== $this->findMappedValue('prefix', $csvRecord)) {
                $member->setPrefix($this->findMappedValue('prefix', $csvRecord));
            }
            if (null !== $this->findMappedValue('firstName', $csvRecord)) {
                $member->setFirstName($this->findMappedValue('firstName', $csvRecord));
            }
            if (null !== $this->findMappedValue('preferredName', $csvRecord)) {
                $member->setPreferredName($this->findMappedValue('preferredName', $csvRecord));
            }
            if (null !== $this->findMappedValue('middleName', $csvRecord)) {
                if (null != $member->getMiddleName() || '' != $this->findMappedValue('middleName', $csvRecord)) {
                    $member->setMiddleName($this->findMappedValue('middleName', $csvRecord));
                }
            }
            if (null !== $this->findMappedValue('lastName', $csvRecord)) {
                $member->setLastName($this->findMappedValue('lastName', $csvRecord));
            }
            if (null !== $this->findMappedValue('suffix', $csvRecord)) {
                $member->setSuffix($this->findMappedValue('suffix', $csvRecord));
            }
            if ($this->findMappedValue('birthDate', $csvRecord) && strtotime($this->findMappedValue('birthDate', $csvRecord))) {
                $member->setBirthDate(new \DateTime($this->findMappedValue('birthDate', $csvRecord)));
            }
            if ($this->findMappedValue('joinDate', $csvRecord) && strtotime($this->findMappedValue('joinDate', $csvRecord))) {
                $member->setJoinDate(new \DateTime($this->findMappedValue('joinDate', $csvRecord)));
            }
            if (null !== $this->findMappedValue('classYear', $csvRecord) && '' !== $this->findMappedValue('classYear', $csvRecord)) {
                if (0 === (int) $this->findMappedValue('classYear', $csvRecord)) {
                    $this->errors[$i][] = sprintf(
                        'Invalid class year: %s',
                        $this->findMappedValue('classYear', $csvRecord)
                    );
                } else {
                    $member->setClassYear((int) $this->findMappedValue('classYear', $csvRecord));
                }
            }
            if (null !== $this->findMappedValue('employer', $csvRecord)) {
                $member->setEmployer($this->findMappedValue('employer', $csvRecord));
            }
            if (null !== $this->findMappedValue('jobTitle', $csvRecord)) {
                $member->setJobTitle($this->findMappedValue('jobTitle', $csvRecord));
            }
            if (null !== $this->findMappedValue('occupation', $csvRecord)) {
                $member->setJobTitle($this->findMappedValue('occupation', $csvRecord));
            }
            if (null !== $this->findMappedValue('primaryEmail', $csvRecord)) {
                $member->setPrimaryEmail($this->findMappedValue('primaryEmail', $csvRecord));
            }
            if (null !== $this->findMappedValue('primaryTelephoneNumber', $csvRecord)) {
                $member->setPrimaryTelephoneNumber($this->findMappedValue('primaryTelephoneNumber', $csvRecord));
            }
            if (null !== $this->findMappedValue('mailingAddress', $csvRecord)) {
                $mailingAddress = explode("\n", $this->findMappedValue('mailingAddress', $csvRecord));
                $member->setMailingAddressLine1($mailingAddress[0]);
                $member->setMailingAddressLine2(isset($mailingAddress[1]) ? $mailingAddress[1] : '');
            } else {
                if (null !== $this->findMappedValue('mailingAddressLine1', $csvRecord)) {
                    $member->setMailingAddressLine1($this->findMappedValue('mailingAddressLine1', $csvRecord));
                }
                if (null !== $this->findMappedValue('mailingAddressLine2', $csvRecord)) {
                    $member->setMailingAddressLine2($this->findMappedValue('mailingAddressLine2', $csvRecord));
                }
            }
            if (null !== $this->findMappedValue('mailingCity', $csvRecord)) {
                $member->setMailingCity($this->findMappedValue('mailingCity', $csvRecord));
            }
            if (null !== $this->findMappedValue('mailingState', $csvRecord)) {
                $member->setMailingState($this->findMappedValue('mailingState', $csvRecord));
            }
            if (null !== $this->findMappedValue('mailingPostalCode', $csvRecord)) {
                $member->setMailingPostalCode($this->findMappedValue('mailingPostalCode', $csvRecord));
            }
            if (null !== $this->findMappedValue('mailingCountry', $csvRecord)) {
                $mailingCountry = $this->findMappedValue('mailingCountry', $csvRecord);
                switch ($mailingCountry) {
                    case 'United States of America':
                    case 'United States':
                    case 'USA':
                    case 'US':
                        $mailingCountry = 'United States';
                        break;
                    case 'Canada':
                    case 'CA':
                        $mailingCountry = 'Canada';
                        break;
                    case 'Mexico':
                    case 'MX':
                        $mailingCountry = 'Mexico';
                        break;
                }
                $member->setMailingCountry($mailingCountry);
            }
            if (null !== $this->findMappedValue('mailingLatitude', $csvRecord) && is_float($this->findMappedValue('mailingLatitude', $csvRecord))) {
                $member->setMailingLatitude($this->findMappedValue('mailingLatitude', $csvRecord));
            }
            if (null !== $this->findMappedValue('mailingLongitude', $csvRecord) && is_float($this->findMappedValue('mailingLongitude', $csvRecord))) {
                $member->setMailingLongitude($this->findMappedValue('mailingLongitude', $csvRecord));
            }
            if (null !== $this->findMappedValue('isDeceased', $csvRecord)) {
                $member->setIsDeceased($this->formatBoolean($this->findMappedValue('isDeceased', $csvRecord)));
            }
            if (null !== $this->findMappedValue('isLost', $csvRecord)) {
                $member->setIsLost($this->formatBoolean($this->findMappedValue('isLost', $csvRecord)));
            }
            if ($this->findMappedValue('isLocalDoNotContact', $csvRecord)) {
                $member->setIsLocalDoNotContact($this->formatBoolean($this->findMappedValue('isLocalDoNotContact', $csvRecord)));
            }
            if (null !== $this->findMappedValue('directoryNotes', $csvRecord)) {
                $member->setDirectoryNotes($this->findMappedValue('directoryNotes', $csvRecord));
            }
            if (null !== $this->findMappedValue('status', $csvRecord)) {
                if (!isset($this->memberStatusMap[$this->findMappedValue('status', $csvRecord)])) {
                    $this->errors[$i][] = sprintf(
                        'status: "%s" does not exist',
                        $this->findMappedValue('status', $csvRecord)
                    );
                } else {
                    $member->setStatus($this->memberStatusMap[$this->findMappedValue('status', $csvRecord)]);
                }
            }
            // If elements empty, populate
            if (!$member->getPreferredName()) {
                $member->setPreferredName($member->getFirstName());
            }

            // Validate records
            $errors = $this->validator->validate($member);
            if (count($errors) > 0) {
                foreach ($errors->getIterator() as $error) {
                    $this->errors[$i][] = sprintf(
                        '%s: %s',
                        $error->getPropertyPath(),
                        $error->getMessage()
                    );
                }
                continue;
            }

            $this->members[$i] = $member;
        }
    }

    public function getAllowedHeaders(): array
    {
        foreach(self::COLUMN_MAPPINGS as $columnMapping) {
            $allowed[] = $columnMapping[0];
        }

        return $allowed;
    }

    private function formatBoolean($bool): bool
    {
        if (is_numeric($bool)) {
            return '1' == $bool;
        }

        if (is_string($bool)) {
            $bool = strtoupper($bool);
            switch ($bool) {
                case 'Y':
                case 'YES':
                case 'ACTIVE':
                case 'TRUE':
                case 'T':
                case 'CHECKED':
                    return true;
                default:
                    return false;
            }
        }

        return (bool) $bool;
    }

    private function loadMemberStatusMapping()
    {
        $memberStatuses = $this->entityManager->getRepository(MemberStatus::class)->findBy([]);
        foreach ($memberStatuses as $memberStatus) {
            $this->memberStatusMap[$memberStatus->getCode()] = $memberStatus;
            $this->memberStatusMap[$memberStatus->getLabel()] = $memberStatus;
        }
    }

    public function findMappedValue($key, $csvRow)
    {
        if (isset(self::COLUMN_MAPPINGS[$key])) {
            foreach (self::COLUMN_MAPPINGS[$key] as $mappedKey => $mappedValue) {
                if (isset($csvRow[$key])) {
                    return trim($csvRow[$key]);
                }
                if (isset($csvRow[$mappedValue])) {
                    return trim($csvRow[$mappedValue]);
                }
            }
        }

        return null;
    }
}
