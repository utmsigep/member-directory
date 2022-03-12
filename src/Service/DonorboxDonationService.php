<?php

namespace App\Service;

use App\Entity\Donation;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader as CsvReader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DonorboxDonationService
{
    public const NAME_HEADER = 'Name';
    public const FIRST_NAME_HEADER = 'Donor First Name';
    public const LAST_NAME_HEADER = 'Donor Last Name';
    public const EMAIL_HEADER = 'Donor Email';
    public const MAKE_DONATION_ANONYMOUS_HEADER = 'Make Donation Anonymous';
    public const CAMPAIGN_HEADER = 'Campaign';
    public const AMOUNT_DESCRIPTION_HEADER = 'Amount Description';
    public const AMOUNT_HEADER = 'Amount';
    public const CURRENCY_HEADER = 'Currency';
    public const PROCESSING_FEE_HEADER = 'Processing Fee';
    public const PLATFORM_FEE_HEADER = 'Platform Fee';
    public const TOTAL_FEE_HEADER = 'Total Fee';
    public const NET_AMOUNT_HEADER = 'Net Amount';
    public const FEE_COVERED_HEADER = 'Fee Covered';
    public const DONOR_COMMENT_HEADER = 'Donor Comment';
    public const INTERNAL_NOTES_HEADER = 'Internal Notes';
    public const DONATED_AT_HEADER = 'Donated At';
    public const PHONE_HEADER = 'Phone';
    public const ADDRESS_HEADER = 'Address';
    public const CITY_HEADER = 'City';
    public const STATE_PROVINCE_HEADER = 'State / Province';
    public const POSTAL_CODE_HEADER = 'Postal Code';
    public const COUNTRY_HEADER = 'Country';
    public const EMPLOYER_HEADER = 'Employer';
    public const OCCUPATION_HEADER = 'Occupation';
    public const DESIGNATION_HEADER = 'Designation';
    public const RECEIPT_ID_HEADER = 'Receipt Id';
    public const DONATION_TYPE_HEADER = 'Donation Type';
    public const CARD_TYPE_HEADER = 'Card Type';
    public const LAST_FOUR_HEADER = 'Last4';
    public const STRIPE_CHARGE_ID_HEADER = 'Stripe Charge Id';
    public const PAYPAL_TRANSACTION_ID_HEADER = 'Paypal Transaction Id';
    public const RECURRING_DONATION_HEADER = 'Recurring Donation';
    public const JOIN_MAILING_LIST_HEADER = 'Join Mailing List';

    protected $entityManager;

    protected $validator;

    protected $donations = [];

    protected $errors = [];

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function getDonations()
    {
        return $this->donations;
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
        $csv = CsvReader::createFromPath($file->getPath().DIRECTORY_SEPARATOR.$file->getFileName(), 'r');
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader(); // returns the CSV header record
        $csvRecords = $csv->getRecords(); // returns all the CSV records as an Iterator object

        // Inspect headers for required fields
        if (!in_array(self::EMAIL_HEADER, $header) ||
            !in_array(self::AMOUNT_HEADER, $header) ||
            !in_array(self::RECEIPT_ID_HEADER, $header)
        ) {
            throw new \Exception('File must have a `Email`, `Amount` and `Receipt Id` set.');
        }

        // Main import loop
        foreach ($csvRecords as $i => $csvRecord) {
            $receiptIdentifier = (isset($csvRecord[self::RECEIPT_ID_HEADER])) ? $csvRecord[self::RECEIPT_ID_HEADER] : null;
            // Find a match record in the database, by Receipt Id
            $donation = $this->entityManager->getRepository(Donation::class)->findOneBy([
                'receiptIdentifier' => $receiptIdentifier,
            ]);
            if (null === $donation) {
                $donation = new Donation();
            }

            // Find member record by email, then name
            $member = null;
            if (isset($csvRecord[self::EMAIL_HEADER])) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'primaryEmail' => $csvRecord[self::EMAIL_HEADER],
                ]);
            }
            if (null === $member && isset($csvRecord[self::FIRST_NAME_HEADER]) && isset($csvRecord[self::LAST_NAME_HEADER])) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'preferredName' => $csvRecord[self::FIRST_NAME_HEADER],
                    'lastName' => $csvRecord[self::LAST_NAME_HEADER],
                ]);
            }
            if (null === $member && isset($csvRecord[self::FIRST_NAME_HEADER]) && isset($csvRecord[self::LAST_NAME_HEADER])) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'firstName' => $csvRecord[self::FIRST_NAME_HEADER],
                    'lastName' => $csvRecord[self::LAST_NAME_HEADER],
                ]);
            }
            if (null === $member) {
                $this->errors[] = sprintf(
                    'Warning: Unable to locate Member record using "%s" or "%s"',
                    $csvRecord[self::EMAIL_HEADER],
                    $csvRecord[self::NAME_HEADER]
                );
            } else {
                $donation->setMember($member);
            }
            if (!$donation->getMember()) {
                $donation->setDonorFirstName($csvRecord[self::FIRST_NAME_HEADER]);
                $donation->setDonorLastName($csvRecord[self::LAST_NAME_HEADER]);
            }
            if (isset($csvRecord[self::RECEIPT_ID_HEADER])) {
                $donation->setReceiptIdentifier($csvRecord[self::RECEIPT_ID_HEADER]);
            }
            if (isset($csvRecord[self::DONATED_AT_HEADER])) {
                $parsedReceivedAt = new \DateTimeImmutable($csvRecord[self::DONATED_AT_HEADER]);
                if (!$donation->getReceivedAt() || $donation->getReceivedAt()->format('Y-m-d') !== $parsedReceivedAt->format('Y-m-d')) {
                    $donation->setReceivedAt($parsedReceivedAt);
                }
            }
            if (isset($csvRecord[self::CAMPAIGN_HEADER])) {
                $donation->setCampaign($csvRecord[self::CAMPAIGN_HEADER]);
            }
            if (isset($csvRecord[self::AMOUNT_HEADER])) {
                if (!$donation->getAmount() || $donation->getAmount() != $this->formatCurrency($csvRecord[self::AMOUNT_HEADER])) {
                    $donation->setAmount($this->formatCurrency($csvRecord[self::AMOUNT_HEADER]));
                }
            }
            if (isset($csvRecord[self::AMOUNT_DESCRIPTION_HEADER])) {
                $donation->setDescription($csvRecord[self::AMOUNT_DESCRIPTION_HEADER]);
            }
            if (isset($csvRecord[self::CURRENCY_HEADER])) {
                $donation->setCurrency($csvRecord[self::CURRENCY_HEADER]);
            }
            // Roll up the Donorbox platform fee into "Processing Fees" rather than tracking separately
            if (isset($csvRecord[self::TOTAL_FEE_HEADER]) && $csvRecord[self::TOTAL_FEE_HEADER]) {
                if (!$donation->getProcessingFee() || $donation->getProcessingFee() != $this->formatCurrency($csvRecord[self::TOTAL_FEE_HEADER])) {
                    $donation->setProcessingFee($this->formatCurrency($csvRecord[self::TOTAL_FEE_HEADER]));
                }
            } elseif (isset($csvRecord[self::PLATFORM_FEE_HEADER]) && isset($csvRecord[self::PROCESSING_FEE_HEADER]) && $csvRecord[self::PLATFORM_FEE_HEADER]) {
                if (!$donation->getProcessingFee() || $donation->getProcessingFee() != $this->formatCurrency($csvRecord[self::PROCESSING_FEE_HEADER]) + $this->formatCurrency($csvRecord[self::PLATFORM_FEE_HEADER])) {
                    $donation->setProcessingFee($this->formatCurrency($csvRecord[self::PROCESSING_FEE_HEADER]) + $this->formatCurrency($csvRecord[self::PLATFORM_FEE_HEADER]));
                }
            } elseif (isset($csvRecord[self::PROCESSING_FEE_HEADER])) {
                if (!$donation->getProcessingFee() || $donation->getProcessingFee() != $this->formatCurrency($csvRecord[self::PROCESSING_FEE_HEADER])) {
                    $donation->setProcessingFee($this->formatCurrency($csvRecord[self::PROCESSING_FEE_HEADER]));
                }
            }
            if (isset($csvRecord[self::NET_AMOUNT_HEADER])) {
                if (!$donation->getNetAmount() || $donation->getNetAmount() != $this->formatCurrency($csvRecord[self::NET_AMOUNT_HEADER])) {
                    $donation->setNetAmount($this->formatCurrency($csvRecord[self::NET_AMOUNT_HEADER]));
                }
            }
            if (isset($csvRecord[self::DONOR_COMMENT_HEADER])) {
                $donation->setDonorComment($csvRecord[self::DONOR_COMMENT_HEADER]);
            }
            if (isset($csvRecord[self::INTERNAL_NOTES_HEADER])) {
                $donation->setInternalNotes($csvRecord[self::INTERNAL_NOTES_HEADER]);
            }
            if (isset($csvRecord[self::DONATION_TYPE_HEADER])) {
                $donation->setDonationType($csvRecord[self::DONATION_TYPE_HEADER]);
            }
            if (isset($csvRecord[self::CARD_TYPE_HEADER])) {
                $donation->setCardType($csvRecord[self::CARD_TYPE_HEADER]);
            }
            if (isset($csvRecord[self::LAST_FOUR_HEADER])) {
                $donation->setLastFour($csvRecord[self::LAST_FOUR_HEADER]);
            }
            if (isset($csvRecord[self::MAKE_DONATION_ANONYMOUS_HEADER])) {
                $donation->setIsAnonymous($this->formatBoolean($csvRecord[self::MAKE_DONATION_ANONYMOUS_HEADER]));
            }
            if (isset($csvRecord[self::RECURRING_DONATION_HEADER])) {
                $donation->setIsRecurring($this->formatBoolean($csvRecord[self::RECURRING_DONATION_HEADER]));
            }

            // Save entire payload
            $donation->setTransactionPayload($csvRecord);

            // Validate records
            $errors = $this->validator->validate($donation);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->errors[$i] = sprintf(
                        '[%s| %s = %01.2f] %s %s',
                        $donation->getReceiptIdentifier(),
                        $donation->getMember()->getDisplayName(),
                        $donation->getAmount(),
                        $error->getPropertyPath(),
                        $error->getMessage()
                    );
                }
                continue;
            }

            $this->donations[$i] = $donation;
        }
    }

    public function getAllowedHeaders(): array
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return array_values($oClass->getConstants());
    }

    private function formatCurrency($string): float
    {
        if (!$string) {
            return 0.0;
        }

        return (float) preg_replace("/[^0-9\.]/", '', $string);
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
}
