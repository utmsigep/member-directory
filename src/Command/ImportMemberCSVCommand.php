<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use League\Csv\Reader as CsvReader;
use Gedmo\Loggable\LoggableListener;

use App\Entity\Member;
use App\Entity\MemberStatus;
use App\Entity\MemberEmail;
use App\Entity\MemberAddress;
use App\Entity\MemberPhoneNumber;

class ImportMemberCSVCommand extends Command
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
    const MAILING_CITY_HEADER = 'mailingCity';
    const MAILING_STATE_HEADER = 'mailingState';
    const MAILING_POSTAL_CODE_HEADER = 'mailingPostalCode';
    const MAILING_COUNTRY_HEADER = 'mailingCountry';
    const LOST_HEADER = 'isLost';
    const LOCAL_DO_NOT_CONTACT_HEADER = 'isLocalDoNotContact';
    const EXTERNAL_DO_NOT_CONTACT_HEADER = 'isExternalDoNotContact';

    const STATUS_MAP = [
        'Brother' => 'UNDERGRADUATE',
        'Alumnus' => 'ALUMNUS',
        'Honorary (Renaissance)' => 'RENAISSANCE',
        'Resigned' => 'RESIGNED',
        'Resigned Pending' => 'RESIGNED',
        'Expelled' => 'EXPELLED',
        'Expelled Pending' => 'EXPELLED',
        'Constituent' => 'OTHER',
        'Quit LM Refund' => 'RESIGNED',
        'Resigned Life Member' => 'RESIGNED',
        'Remove Accepted' => 'RESIGNED',
        'Remove Candidate' => 'RESIGNED'
    ];

    protected static $defaultName = 'import:membercsv';

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Imports Member records from a CSV.')
            ->addArgument('filepath', InputArgument::OPTIONAL, 'Path to CSV to import.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Preview records to import, but do not import.')
            ->addOption('force-status-update', null, InputOption::VALUE_NONE, 'Update status field with new values.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('filepath');
        $dryRun = (bool) $input->getOption('dry-run');
        $forceStatusUpdate = (bool) $input->getOption('force-status-update');

        // Ensure a file is selected
        if (!$filePath) {
            $io->error('You must specify a file to import!');
            return 1;
        }

        if ($dryRun) {
            $io->note('DRY-RUN: Will not import records.');
        }

        // Parse loaded file
        $csv = CsvReader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        $header = $csv->getHeader(); //returns the CSV header record
        $csvRecords = $csv->getRecords(); //returns all the CSV records as an Iterator object

        // Inspect headers for required fields
        if (!in_array(self::LOCAL_IDENTIFIER_HEADER, $header) ||
            !in_array(self::EXTERNAL_IDENTIFIER_HEADER, $header)
        ) {
            $io->error(sprintf(
                'Your CSV header must included either %s or %s',
                self::LOCAL_IDENTIFIER_HEADER,
                self::EXTERNAL_IDENTIFIER_HEADER
            ));
            return 1;
        }

        // Main import loop
        $outputRows = [];
        foreach ($csvRecords as $rowI => $csvRecord) {
            // Find a match record in the database, if exists, by either internal or external identifier
            $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                'localIdentifier' => $csvRecord[self::LOCAL_IDENTIFIER_HEADER]
            ]);
            if ($member === null) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'externalIdentifier' => $csvRecord[self::EXTERNAL_IDENTIFIER_HEADER]
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
                $member->setMiddleName($csvRecord[self::MIDDLE_NAME_HEADER]);
            }
            if (isset($csvRecord[self::LAST_NAME_HEADER])) {
                $member->setLastName($csvRecord[self::LAST_NAME_HEADER]);
            }
            if (isset($csvRecord[self::JOIN_DATE_HEADER])) {
                $member->setJoinDate(new \DateTime($csvRecord[self::JOIN_DATE_HEADER]));
            }
            if (isset($csvRecord[self::CLASS_YEAR_HEADER])) {
                $member->setClassYear((int) $csvRecord[self::CLASS_YEAR_HEADER]);
            }
            if (isset($csvRecord[self::DECEASED_HEADER])) {
                $member->setIsDeceased($this->formatBoolean($csvRecord[self::DECEASED_HEADER]));
            }
            if (isset($csvRecord[self::EMPLOYER_HEADER])) {
                $member->setEmployer($csvRecord[self::EMPLOYER_HEADER]);
            }
            if (isset($csvRecord[self::JOB_TITLE_HEADER])) {
                $member->setJobTitle($csvRecord[self::JOB_TITLE_HEADER]);
            }
            if (isset($csvRecord[self::OCCUPATION_HEADER])) {
                $member->setOccupation($csvRecord[self::OCCUPATION_HEADER]);
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
                $member->setMailingCity($csvRecord[self::MAILING_CITY_HEADER]);
                $member->setMailingState($csvRecord[self::MAILING_STATE_HEADER]);
                $member->setMailingPostalCode($csvRecord[self::MAILING_POSTAL_CODE_HEADER]);
                $member->setMailingCountry(isset($csvRecord[self::MAILING_COUNTRY_HEADER]) ? $csvRecord[self::MAILING_COUNTRY_HEADER] : 'United States');
            }
            if (isset($csvRecord[self::LOST_HEADER])) {
                $member->setIsLost($this->formatBoolean($csvRecord[self::LOST_HEADER]));
            }
            if (isset($csvRecord[self::LOCAL_DO_NOT_CONTACT_HEADER])) {
                $member->setIsLocalDoNotContact($this->formatBoolean($csvRecord[self::LOCAL_DO_NOT_CONTACT_HEADER]));
            }
            if (isset($csvRecord[self::EXTERNAL_DO_NOT_CONTACT_HEADER])) {
                $member->setIsExternalDoNotContact($this->formatBoolean($csvRecord[self::EXTERNAL_DO_NOT_CONTACT_HEADER]));
            }
            if (isset($csvRecord[self::STATUS_HEADER])) {
                $memberStatus = $this->entityManager->getRepository(MemberStatus::class)->findOneBy([
                    'code' => self::STATUS_MAP[$csvRecord[self::STATUS_HEADER]]
                    ? self::STATUS_MAP[$csvRecord[self::STATUS_HEADER]]
                    : null
                ]);
                if ($memberStatus === null) {
                    $io->error(sprintf(
                        '[%s|%s %s, %s] Unable to set status to "%s" (not mapped)',
                        $member->getExternalIdentifier(),
                        $member->getLocalIdentifier(),
                        $member->getLastName(),
                        $member->getFirstName(),
                        $csvRecord[self::STATUS_HEADER]
                    ));
                    continue;
                }
                if (!$forceStatusUpdate && $member->getStatus() && $member->getStatus() !== $memberStatus) {
                    $io->error(sprintf(
                        '[%s|%s %s, %s] Status update from %s to %s denied.',
                        $member->getExternalIdentifier(),
                        $member->getLocalIdentifier(),
                        $member->getLastName(),
                        $member->getFirstName(),
                        $member->getStatus()->getCode(),
                        $memberStatus->getCode()
                    ));
                } else {
                    $member->setStatus($memberStatus);
                }
            }

            // Validate records
            $errors = $this->validator->validate($member);
            if (count($errors) > 0) {
                foreach ($errors->getIterator() as $error) {
                    $io->error(sprintf(
                        '[%s|%s %s, %s] %s %s',
                        $member->getExternalIdentifier(),
                        $member->getLocalIdentifier(),
                        $member->getLastName(),
                        $member->getFirstName(),
                        $error->getPropertyPath(),
                        $error->getMessage()
                    ));
                }
                continue;
            }

            // Persist record in the database if not dry-run
            if (!$dryRun) {
                $this->entityManager->persist($member);
            }

            // Set values for script output
            $outputRows[] = [
                $member->getExternalIdentifier(),
                $member->getLocalIdentifier(),
                $member->getFirstName(),
                $member->getPreferredName(),
                $member->getMiddleName(),
                $member->getLastName(),
                $member->getStatus()->getLabel()
            ];
        }

        // Print output table
        $io->table(
            [
                'External Identifier',
                'Local Identifier',
                'First Name',
                'Preferred Name',
                'Middle Name',
                'Last Name',
                'Status'
            ],
            $outputRows
        );

        // Save records in the database
        if (!$dryRun) {
            $this->entityManager->flush();
        } else {
            $io->note('This was a dry-run of the importer. No records were imported.');
        }

        $io->success('Done!');
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
                    break;
                default:
                    return false;
            }
        }
        return (bool) $bool;
    }

}
