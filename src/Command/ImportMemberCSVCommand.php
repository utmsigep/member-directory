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

use App\Entity\Member;
use App\Entity\MemberStatus;
use App\Entity\MemberEmail;
use App\Entity\MemberAddress;
use App\Entity\MemberPhoneNumber;

class ImportMemberCSVCommand extends Command
{
    const LOCAL_IDENTIFIER_HEADER = 'Brother ID';
    const EXTERNAL_IDENTIFIER_HEADER = '18 digit ID';
    const FIRST_NAME_HEADER = 'First Name';
    const MIDDLE_NAME_HEADER = 'Middle Name';
    const PREFERRED_NAME_HEADER = 'Nickname';
    const LAST_NAME_HEADER = 'Last Name';
    const STATUS_HEADER = 'Member Status';
    const JOIN_DATE_HEADER = 'Joined Date';
    const CLASS_YEAR_HEADER = 'Class';
    const DECEASED_HEADER = 'Deceased';
    const EMPLOYER_HEADER = 'Employer';
    const JOB_TITLE_HEADER = 'Title';
    const OCCUPATION_HEADER = 'Occupation';
    const EMAIL_HEADER = 'Email';
    const ADDRESS_HEADER = 'Mailing Street';
    const CITY_HEADER = 'Mailing City';
    const STATE_HEADER = 'Mailing State/Province';
    const COUNTRY_HEADER = 'Mailing Country';
    const POSTAL_CODE_HEADER = 'Mailing Zip/Postal Code';
    const HOME_PHONE_NUMBER_HEADER = 'Home Phone';
    const WORK_PHONE_NUMBER_HEADER = 'Work Phone';
    const MOBILE_PHONE_NUMBER_HEADER = 'Mobile';
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
                $member->setIsDeceased((bool) $csvRecord[self::DECEASED_HEADER]);
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
            if (isset($csvRecord[self::EMAIL_HEADER]) && $csvRecord[self::EMAIL_HEADER]) {
                $memberEmail = new MemberEmail();
                $memberEmail->setLabel('Home');
                $memberEmail->setEmail($csvRecord[self::EMAIL_HEADER]);
                $memberEmail->setSort(0);
                $member->addMemberEmail($memberEmail);
            }

            if (isset($csvRecord[self::ADDRESS_HEADER]) && $csvRecord[self::ADDRESS_HEADER]) {
                $memberAddress = new MemberAddress();
                $memberAddress->setLabel('Home');
                $addressLines = explode("\n", $csvRecord[self::ADDRESS_HEADER]);
                $memberAddress->setAddressLine1($addressLines[0]);
                $memberAddress->setAddressLine2(isset($addressLines[1]) ? $addressLines[1] : '');
                $memberAddress->setCity($csvRecord[self::CITY_HEADER]);
                $memberAddress->setState($csvRecord[self::STATE_HEADER]);
                $memberAddress->setPostalCode($csvRecord[self::POSTAL_CODE_HEADER]);
                $memberAddress->setCountry($csvRecord[self::COUNTRY_HEADER]);
                $memberAddress->setSort(0);
                $member->addMemberAddress($memberAddress);
            }

            if (isset($csvRecord[self::HOME_PHONE_NUMBER_HEADER]) && $csvRecord[self::HOME_PHONE_NUMBER_HEADER]) {
                $memberPhoneNumber = new MemberPhoneNumber();
                $memberPhoneNumber->setLabel('Home');
                $memberPhoneNumber->setPhoneNumber($csvRecord[self::HOME_PHONE_NUMBER_HEADER]);
                $memberPhoneNumber->setIsSMS(false);
                $memberPhoneNumber->setSort(0);
                $member->addMemberPhoneNumber($memberPhoneNumber);
            }

            if (isset($csvRecord[self::WORK_PHONE_NUMBER_HEADER]) && $csvRecord[self::WORK_PHONE_NUMBER_HEADER]) {
                $memberPhoneNumber = new MemberPhoneNumber();
                $memberPhoneNumber->setLabel('Work');
                $memberPhoneNumber->setPhoneNumber($csvRecord[self::WORK_PHONE_NUMBER_HEADER]);
                $memberPhoneNumber->setIsSMS(false);
                $memberPhoneNumber->setSort(0);
                $member->addMemberPhoneNumber($memberPhoneNumber);
            }

            if (isset($csvRecord[self::MOBILE_PHONE_NUMBER_HEADER]) && $csvRecord[self::MOBILE_PHONE_NUMBER_HEADER]) {
                $memberPhoneNumber = new MemberPhoneNumber();
                $memberPhoneNumber->setLabel('Mobile');
                $memberPhoneNumber->setPhoneNumber($csvRecord[self::MOBILE_PHONE_NUMBER_HEADER]);
                $memberPhoneNumber->setIsSMS(true);
                $memberPhoneNumber->setSort(0);
                $member->addMemberPhoneNumber($memberPhoneNumber);
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
}
