<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use App\Service\EmailService;
use App\Entity\Member;

class ContactRatingService
{
    const EMAIL_DAYS_AGO = 90;

    protected $emailService;
    protected $logger;

    public function __construct(EmailService $emailService, LoggerInterface $logger)
    {
        $this->emailService = $emailService;
        $this->logger = $logger;
    }

    public function scoreMember(Member $member): Member
    {
        // General scoring
        $score = 0;
        $scoringTable = [
            'NOT_DO_NOT_CONTACT' => 50,
            'NOT_LOST' => 30,
            'HAS_EMAIL' => 40,
            'HAS_PHONE' => 20,
            'HAS_FACEBOOK' => 20,
            'HAS_MAILING_ADDRESS' => 30,
            'HAS_EMPLOYER' => 5,
            'HAS_JOB_TITLE' => 5,
            'HAS_OCCUPATION' => 5,
        ];

        $score += (!$member->getIsLocalDoNotContact()) ? $scoringTable['NOT_DO_NOT_CONTACT'] : 0;
        $score += (!$member->getIsLost()) ? $scoringTable['NOT_LOST'] : 0;
        $score += ($member->getPrimaryEmail()) ? $scoringTable['HAS_EMAIL'] : 0;
        $score += ($member->getPrimaryTelephoneNumber()) ? $scoringTable['HAS_PHONE'] : 0;
        $score += ($member->getFacebookIdentifier()) ? $scoringTable['HAS_FACEBOOK'] : 0;
        $score += (($member->getMailingAddressLine1() || $member->getMailingAddressLine2()) && $member->getMailingPostalCode()) ? $scoringTable['HAS_MAILING_ADDRESS'] : 0;
        $score += ($member->getEmployer()) ? $scoringTable['HAS_EMPLOYER'] : 0;
        $score += ($member->getJobTitle()) ? $scoringTable['HAS_JOB_TITLE'] : 0;
        $score += ($member->getOccupation()) ? $scoringTable['HAS_OCCUPATION'] : 0;

        // Add email service scoring only if configured
        if ($this->emailService->isConfigured()) {
            $scoringTable['IS_ACTIVE_SUBSCRIBER'] = 30;
            $scoringTable['HAS_OPENED_EMAIL'] = 5;
            $scoringTable['HAS_CLICKED_EMAIL'] = 10;

            $subscription = $this->emailService->getMemberSubscription($member);

            // IS_ACTIVE_SUBSCRIBER
            if (is_object($subscription)
                && property_exists($subscription, 'State')
                && $subscription->State == 'Active'
            ) {
                $score += $scoringTable['IS_ACTIVE_SUBSCRIBER'];
            }

            // HAS_OPENED_EMAIL and HAS_CLICKED_EMAIL
            $hasOpened = false;
            $hasClicked = false;

            if (is_object($subscription) && property_exists($subscription, 'State')) {
                $subscriptionHistory = $this->emailService->getMemberSubscriptionHistory($member);
                if (is_array($subscriptionHistory)) {
                    foreach ($subscriptionHistory as $campaign) {
                        foreach ($campaign->Actions as $action) {
                            // Only evaluate campaigns from recent past
                            if (strtotime($action->Date) < strtotime(sprintf('-%d days', self::EMAIL_DAYS_AGO))) {
                                continue;
                            }
                            if ($action->Event == 'Open') {
                                $hasOpened = true;
                            }
                            if ($action->Event == 'Click') {
                                $hasClicked = true;
                            }
                        }
                    }
                }
            }

            $score += ($hasOpened) ? $scoringTable['HAS_OPENED_EMAIL'] : 0;
            $score += ($hasClicked) ? $scoringTable['HAS_CLICKED_EMAIL'] : 0;
        }

        // Calculate total possible score
        $possibleScore = array_sum($scoringTable);

        $this->logger->info('Contact Rating Scoring', [
            'member' => $member->getDisplayName(),
            'total' => $score,
            'possible' => $possibleScore
        ]);

        $member->setContactRating($score/$possibleScore);
        return $member;
    }
}
