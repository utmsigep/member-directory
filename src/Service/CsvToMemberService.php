<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Csv\Reader as CsvReader;

use App\Entity\Member;
use App\Entity\MemberStatus;
use App\Entity\MemberEmail;
use App\Entity\MemberAddress;
use App\Entity\MemberPhoneNumber;

class CsvToMemberService
{
    const LOCAL_IDENTIFIER_HEADER = 'localIdentifier';
    const EXTERNAL_IDENTIFIER_HEADER = 'externalIdentifier';
    const FIRST_NAME_HEADER = 'firstName';
    const MIDDLE_NAME_HEADER = 'middleName';
    const PREFERRED_NAME_HEADER = 'preferredName';
    const LAST_NAME_HEADER = 'lastName';
    const STATUS_HEADER = 'status';
    const JOIN_DATE_HEADER = 'joinDate';
    const CLASS_YEAR_HEADER = 'classYear';
    const DECEASED_HEADER = 'isDeceased';
    const EMPLOYER_HEADER = 'employer';
    const JOB_TITLE_HEADER = 'jobTitle';
    const OCCUPATION_HEADER = 'occupation';
    const PRIMARY_EMAIL_HEADER = 'primaryEmail';
    const PRIMARY_TELEPHONE_NUMBER_HEADER = 'primaryTelephoneNumber';
    const MAILING_ADDRESS_HEADER = 'mailingAddress';
    const MAILING_ADDRESS_LINE1_HEADER = 'mailingAddressLine1';
    const MAILING_ADDRESS_LINE2_HEADER = 'mailingAddressLine2';
    const MAILING_CITY_HEADER = 'mailingCity';
    const MAILING_STATE_HEADER = 'mailingState';
    const MAILING_POSTAL_CODE_HEADER = 'mailingPostalCode';
    const MAILING_COUNTRY_HEADER = 'mailingCountry';
    const MAILING_LATITUDE_HEADER = 'mailingLatitude';
    const MAILING_LONGITUDE_HEADER = 'mailingLongitude';
    const LOST_HEADER = 'isLost';
    const LOCAL_DO_NOT_CONTACT_HEADER = 'isLocalDoNotContact';
    const DIRECTORY_NOTES_HEADER = 'directoryNotes';

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
        return $this->errors;
    }

    public function run(UploadedFile $file)
    {
        // Ensure a file is selected
        if (!$file->isValid()) {
            throw new \Exception('Uploaded file is invalid.');
        }

        // Parse loaded file
        $csv = CsvReader::createFromPath($file->getPath() . DIRECTORY_SEPARATOR . $file->getFileName(), 'r');
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader(); // returns the CSV header record
        $csvRecords = $csv->getRecords(); //returns all the CSV records as an Iterator object

        // Inspect headers for required fields
        if (!in_array(self::LOCAL_IDENTIFIER_HEADER, $header) &&
            !in_array(self::EXTERNAL_IDENTIFIER_HEADER, $header)
        ) {
            throw new \Exception('File must have a localIdentifier or externalIdentifier set.');
        }

        // Main import loop
        foreach ($csvRecords as $i => $csvRecord) {
            $externalIdentifier = (isset($csvRecord[self::EXTERNAL_IDENTIFIER_HEADER])) ? $csvRecord[self::EXTERNAL_IDENTIFIER_HEADER] : null;
            $localIdentifier = (isset($csvRecord[self::LOCAL_IDENTIFIER_HEADER])) ? $csvRecord[self::LOCAL_IDENTIFIER_HEADER] : null;
            // Find a match record in the database, if exists, by either internal or external identifier
            $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                'externalIdentifier' => $externalIdentifier
            ]);
            if ($member === null) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'localIdentifier' => $localIdentifier
                ]);
                if ($member === null) {
                    $member = new Member();
                }
            }

            // Populate fields if set
            if (isset($csvRecord[self::EXTERNAL_IDENTIFIER_HEADER])) {
                $member->setExternalIdentifier($csvRecord[self::EXTERNAL_IDENTIFIER_HEADER]);
            }
            if (isset($csvRecord[self::LOCAL_IDENTIFIER_HEADER])) {
                $member->setLocalIdentifier($csvRecord[self::LOCAL_IDENTIFIER_HEADER]);
            }
            if (isset($csvRecord[self::FIRST_NAME_HEADER])) {
                $member->setFirstName($csvRecord[self::FIRST_NAME_HEADER]);
            }
            if (isset($csvRecord[self::PREFERRED_NAME_HEADER])) {
                $member->setPreferredName($csvRecord[self::PREFERRED_NAME_HEADER]);
            }
            if (isset($csvRecord[self::MIDDLE_NAME_HEADER])) {
                if ($member->getMiddleName() != null || $csvRecord[self::MIDDLE_NAME_HEADER] != '') {
                    $member->setMiddleName($csvRecord[self::MIDDLE_NAME_HEADER]);
                }
            }
            if (isset($csvRecord[self::LAST_NAME_HEADER])) {
                $member->setLastName($csvRecord[self::LAST_NAME_HEADER]);
            }
            if (isset($csvRecord[self::JOIN_DATE_HEADER])) {
                $member->setJoinDate(new \DateTime($csvRecord[self::JOIN_DATE_HEADER]));
            }
            if (isset($csvRecord[self::CLASS_YEAR_HEADER])) {
                if ($member->getClassYear() != null || $csvRecord[self::CLASS_YEAR_HEADER] != 0) {
                    $member->setClassYear((int) $csvRecord[self::CLASS_YEAR_HEADER]);
                }
            }
            if (isset($csvRecord[self::DECEASED_HEADER])) {
                $member->setIsDeceased($this->formatBoolean($csvRecord[self::DECEASED_HEADER]));
            }
            if (isset($csvRecord[self::EMPLOYER_HEADER])) {
                if ($member->getEmployer() != null || $csvRecord[self::EMPLOYER_HEADER] != '') {
                    $member->setEmployer($csvRecord[self::EMPLOYER_HEADER]);
                }
            }
            if (isset($csvRecord[self::JOB_TITLE_HEADER])) {
                if ($member->getJobTitle() != null || $csvRecord[self::JOB_TITLE_HEADER] != '') {
                    $member->setJobTitle($csvRecord[self::JOB_TITLE_HEADER]);
                }
            }
            if (isset($csvRecord[self::OCCUPATION_HEADER])) {
                if ($member->getOccupation() != null || $csvRecord[self::OCCUPATION_HEADER] != '') {
                    $member->setOccupation($csvRecord[self::OCCUPATION_HEADER]);
                }
            }
            if (isset($csvRecord[self::PRIMARY_EMAIL_HEADER]) && $csvRecord[self::PRIMARY_EMAIL_HEADER]) {
                $member->setPrimaryEmail($csvRecord[self::PRIMARY_EMAIL_HEADER]);
            }
            if (isset($csvRecord[self::PRIMARY_TELEPHONE_NUMBER_HEADER]) && $csvRecord[self::PRIMARY_TELEPHONE_NUMBER_HEADER]) {
                $member->setPrimaryTelephoneNumber($csvRecord[self::PRIMARY_TELEPHONE_NUMBER_HEADER]);
            }
            if (isset($csvRecord[self::MAILING_ADDRESS_HEADER]) && $csvRecord[self::MAILING_ADDRESS_HEADER]) {
                $addressLines = explode("\n", $csvRecord[self::MAILING_ADDRESS_HEADER]);
                $member->setMailingAddressLine1($addressLines[0]);
                $member->setMailingAddressLine2(isset($addressLines[1]) ? $addressLines[1] : '');
            }
            if (isset($csvRecord[self::MAILING_ADDRESS_LINE1_HEADER]) && $csvRecord[self::MAILING_ADDRESS_LINE1_HEADER]) {
                $member->setMailingAddressLine1($csvRecord[self::MAILING_ADDRESS_LINE1_HEADER]);
            }
            if (isset($csvRecord[self::MAILING_ADDRESS_LINE2_HEADER]) && $csvRecord[self::MAILING_ADDRESS_LINE2_HEADER]) {
                $member->setMailingAddressLine2($csvRecord[self::MAILING_ADDRESS_LINE2_HEADER]);
            }
            if (isset($csvRecord[self::MAILING_CITY_HEADER]) && $csvRecord[self::MAILING_CITY_HEADER]) {
                $member->setMailingCity($csvRecord[self::MAILING_CITY_HEADER]);
            }
            if (isset($csvRecord[self::MAILING_STATE_HEADER]) && $csvRecord[self::MAILING_STATE_HEADER]) {
                $member->setMailingState($csvRecord[self::MAILING_STATE_HEADER]);
            }
            if (isset($csvRecord[self::MAILING_POSTAL_CODE_HEADER]) && $csvRecord[self::MAILING_POSTAL_CODE_HEADER]) {
                $member->setMailingPostalCode($csvRecord[self::MAILING_POSTAL_CODE_HEADER]);
            }
            if (isset($csvRecord[self::MAILING_COUNTRY_HEADER]) && $csvRecord[self::MAILING_COUNTRY_HEADER]) {
                $mailingCountry = $csvRecord[self::MAILING_COUNTRY_HEADER];
                if ($mailingCountry == 'US') {
                    $mailingCountry = 'United States';
                }
                $member->setMailingCountry($mailingCountry);
            }
            if (isset($csvRecord[self::MAILING_LATITUDE_HEADER]) && $csvRecord[self::MAILING_LATITUDE_HEADER]) {
                $member->setMailingLatitude($csvRecord[self::MAILING_LATITUDE_HEADER]);
            }
            if (isset($csvRecord[self::MAILING_LONGITUDE_HEADER]) && $csvRecord[self::MAILING_LONGITUDE_HEADER]) {
                $member->setMailingLongitude($csvRecord[self::MAILING_LONGITUDE_HEADER]);
            }
            if (isset($csvRecord[self::LOST_HEADER])) {
                $member->setIsLost($this->formatBoolean($csvRecord[self::LOST_HEADER]));
            }
            if (isset($csvRecord[self::LOCAL_DO_NOT_CONTACT_HEADER])) {
                $member->setIsLocalDoNotContact($this->formatBoolean($csvRecord[self::LOCAL_DO_NOT_CONTACT_HEADER]));
            }
            if (isset($csvRecord[self::DIRECTORY_NOTES_HEADER])) {
                $member->setDirectoryNotes($csvRecord[self::DIRECTORY_NOTES_HEADER]);
            }
            if (isset($csvRecord[self::STATUS_HEADER])) {
                if (!isset($this->memberStatusMap[$csvRecord[self::STATUS_HEADER]])) {
                    $this->errors[] = sprintf(
                        '[%s|%s %s, %s] Unable to set status to "%s" (not mapped)',
                        $member->getExternalIdentifier(),
                        $member->getLocalIdentifier(),
                        $member->getLastName(),
                        $member->getPreferredName(),
                        $csvRecord[self::STATUS_HEADER]
                    );
                    continue;
                }
                $member->setStatus($this->memberStatusMap[$csvRecord[self::STATUS_HEADER]]);
            }
            // If elements empty, populate
            if (!$member->getPreferredName()) {
                $member->setPreferredName($member->getFirstName());
            }

            // Validate records
            $errors = $this->validator->validate($member);
            if (count($errors) > 0) {
                foreach ($errors->getIterator() as $error) {
                    $this->errors[$i] = sprintf(
                        '[%s|%s %s, %s] %s %s',
                        $member->getExternalIdentifier(),
                        $member->getLocalIdentifier(),
                        $member->getLastName(),
                        $member->getPreferredName(),
                        $error->getPropertyPath(),
                        $error->getMessage()
                    );
                }
                continue;
            }

            $this->members[$i] = $member;
        }
    }

    private function formatBoolean($bool): bool
    {
        if (is_numeric($bool)) {
            return $bool == '1';
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

}
