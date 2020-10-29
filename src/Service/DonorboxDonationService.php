<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Csv\Reader as CsvReader;

use App\Entity\Donation;
use App\Entity\Member;

class DonorboxDonationService
{
    const NAME_HEADER = 'Name';
    const FIRST_NAME_HEADER = 'Donor First Name';
    const LAST_NAME_HEADER = 'Donor Last Name';
    const EMAIL_HEADER = 'Donor Email';
    const MAKE_DONATION_ANONYMOUS_HEADER = 'Make Donation Anonymous';
    const CAMPAIGN_HEADER = 'Campaign';
    const AMOUNT_DESCRIPTION_HEADER = 'Amount Description';
    const AMOUNT_HEADER = 'Amount';
    const CURRENCY_HEADER = 'Currency';
    const PROCESSING_FEE_HEADER = 'Processing Fee';
    const PLATFORM_FEE_HEADER = 'Platform Fee';
    const TOTAL_FEE_HEADER = 'Total Fee';
    const NET_AMOUNT_HEADER = 'Net Amount';
    const FEE_COVERED_HEADER = 'Fee Covered';
    const DONOR_COMMENT_HEADER = 'Donor Comment';
    const INTERNAL_NOTES_HEADER = 'Internal Notes';
    const DONATED_AT_HEADER = 'Donated At';
    const PHONE_HEADER = 'Phone';
    const ADDRESS_HEADER = 'Address';
    const CITY_HEADER = 'City';
    const STATE_PROVINCE_HEADER = 'State / Province';
    const POSTAL_CODE_HEADER = 'Postal Code';
    const COUNTRY_HEADER = 'Country';
    const EMPLOYER_HEADER = 'Employer';
    const OCCUPATION_HEADER = 'Occupation';
    const DESIGNATION_HEADER = 'Designation';
    const RECEIPT_ID_HEADER = 'Receipt Id';
    const DONATION_TYPE_HEADER = 'Donation Type';
    const CARD_TYPE_HEADER = 'Card Type';
    const LAST_FOUR_HEADER = 'Last4';
    const STRIPE_CHARGE_ID_HEADER = 'Stripe Charge Id';
    const PAYPAL_TRANSACTION_ID_HEADER = 'Paypal Transaction Id';
    const RECURRING_DONATION_HEADER = 'Recurring Donation';
    const JOIN_MAILING_LIST_HEADER = 'Join Mailing List';

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
        $csv = CsvReader::createFromPath($file->getPath() . DIRECTORY_SEPARATOR . $file->getFileName(), 'r');
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader(); // returns the CSV header record
        $csvRecords = $csv->getRecords(); //returns all the CSV records as an Iterator object

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
                'receiptIdentifier' => $receiptIdentifier
            ]);
            if ($donation === null) {
                $donation = new Donation();
            }

            // Find member record by email, then name
            $member = null;
            if (isset($csvRecord[self::EMAIL_HEADER])) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'primaryEmail' => $csvRecord[self::EMAIL_HEADER]
                ]);
            }
            if ($member === null && isset($csvRecord[self::FIRST_NAME_HEADER]) && isset($csvRecord[self::LAST_NAME_HEADER])) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'preferredName' => $csvRecord[self::FIRST_NAME_HEADER],
                    'lastName' => $csvRecord[self::LAST_NAME_HEADER]
                ]);
            }
            if ($member === null && isset($csvRecord[self::FIRST_NAME_HEADER]) && isset($csvRecord[self::LAST_NAME_HEADER])) {
                $member = $this->entityManager->getRepository(Member::class)->findOneBy([
                    'firstName' => $csvRecord[self::FIRST_NAME_HEADER],
                    'lastName' => $csvRecord[self::LAST_NAME_HEADER]
                ]);
            }
            if ($member === null) {
                $this->errors[] = sprintf(
                    'Unable to locate member record using: "%s" or "%s"; Skipped.',
                    $csvRecord[self::EMAIL_HEADER],
                    $csvRecord[self::NAME_HEADER]
                );
                continue;
            }
            $donation->setMember($member);

            if (isset($csvRecord[self::RECEIPT_ID_HEADER])) {
                $donation->setReceiptIdentifier($csvRecord[self::RECEIPT_ID_HEADER]);
            }
            if (isset($csvRecord[self::DONATED_AT_HEADER])) {
                $donation->setReceivedAt(new \DateTime($csvRecord[self::DONATED_AT_HEADER]));
            }
            if (isset($csvRecord[self::CAMPAIGN_HEADER])) {
                $donation->setCampaign($csvRecord[self::CAMPAIGN_HEADER]);
            }
            if (isset($csvRecord[self::AMOUNT_HEADER])) {
                $donation->setAmount((float) $csvRecord[self::AMOUNT_HEADER]);
            }
            if (isset($csvRecord[self::AMOUNT_DESCRIPTION_HEADER])) {
                $donation->setDescription($csvRecord[self::AMOUNT_DESCRIPTION_HEADER]);
            }
            if (isset($csvRecord[self::CURRENCY_HEADER])) {
                $donation->setCurrency($csvRecord[self::CURRENCY_HEADER]);
            }
            // Roll up the Donorbox platform fee into "Processing Fees" rather than tracking separately
            if (isset($csvRecord[self::TOTAL_FEE_HEADER]) && $csvRecord[self::TOTAL_FEE_HEADER]) {
                $donation->setProcessingFee((float) $csvRecord[self::TOTAL_FEE_HEADER]);
            } elseif (isset($csvRecord[self::PLATFORM_FEE_HEADER]) && isset($csvRecord[self::PROCESSING_FEE_HEADER]) && $csvRecord[self::PLATFORM_FEE_HEADER]) {
                $donation->setProcessingFee((float) $csvRecord[self::PROCESSING_FEE_HEADER] + (float) $csvRecord[self::PLATFORM_FEE_HEADER]);
            } elseif (isset($csvRecord[self::PROCESSING_FEE_HEADER])) {
                $donation->setProcessingFee((float) $csvRecord[self::PROCESSING_FEE_HEADER]);
            }
            if (isset($csvRecord[self::NET_AMOUNT_HEADER])) {
                $donation->setNetAmount((float) $csvRecord[self::NET_AMOUNT_HEADER]);
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
                foreach ($errors->getIterator() as $error) {
                    $this->errors[$i] = sprintf(
                        '[%s| %s = %01.2f] %s %s',
                        $member->getReceiptIdentifier(),
                        $member->getMember()->getDisplayName(),
                        $member->getAmount(),
                        $error->getPropertyPath(),
                        $error->getMessage()
                    );
                }
                continue;
            }

            $this->donations[$i] = $donation;
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
}
