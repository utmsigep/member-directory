<?php

namespace App\Service;

use CS_REST_Subscribers;

use App\Entity\Member;

class EmailService
{
    protected $apiKey;

    protected $defaultListId;

    protected $client;

    public function __construct()
    {
        $this->apiKey = $_ENV['CAMPAIGN_MONITOR_API_KEY'];
        $this->defaultListId = $_ENV['CAMPAIGN_MONITOR_DEFAULT_LIST_ID'];
        $this->client = new CS_REST_Subscribers(
            $this->defaultListId,
            [
                'api_key' => $this->apiKey
            ]
        );
    }

    public function getMemberSubscription(Member $member): array
    {
        if (!$member->getPrimaryEmail()) {
            return [];
        }

        $result = $this->client->get($member->getPrimaryEmail(), true);
        return $result->response;
    }

    public function getMemberSubscriptionHistory(Member $member): object
    {
        if (!$member->getPrimaryEmail()) {
            return [];
        }
        $result = $this->client->get_history($member->getPrimaryEmail());
        return $result->response;
    }

    public function subscribeMember(Member $member): boolean
    {
        if (!$member->getPrimaryEmail()) {
            return false;
        }
        $result = $this->client->add([
            'EmailAddress' => $member->getPrimaryEmail(),
            'Name' => $member->getDisplayName(),
            'Custom Fields' => $this->builtCustomFieldArray($member),
            'ConsentToTrack' => true,
            'Resubscribe' => true
        ]);
        if ($result->was_successful()) {
            return true;
        }
        return false;
    }

    /* Private Methods */

    private function buildCustomFieldArray(Member $member): array
    {
        return [
            [
                'Key' => 'firstName',
                'Value' => $member->getFirstName()
            ],
            [
                'Key' => 'preferredName',
                'Value' => $member->getPreferredName()
            ],
            [
                'Key' => 'middleName',
                'Value' => $member->getMiddleName()
            ],
            [
                'Key' => 'lastName',
                'Value' => $member->getLastName()
            ],
            [
                'Key' => 'localIdentifier',
                'Value' => $member->getLocalIdentifier()
            ],
            [
                'Key' => 'localIdentifierShort',
                'Value' => $member->getLocalIdentifierShort()
            ],
            [
                'Key' => 'externalIdentifier',
                'Value' => $member->getExternalIdentifier()
            ],
            [
                'Key' => 'primaryTelephoneNumber',
                'Value' => $member->getPrimaryTelephoneNumber()
            ],
            [
                'Key' => 'mailingAddressLine1',
                'Value' => $member->getMailingAddressLine1()
            ],
            [
                'Key' => 'mailingAddressLine2',
                'Value' => $member->getMailingAddressLine2()
            ],
            [
                'Key' => 'mailingCity',
                'Value' => $member->getMailingCity()
            ],
            [
                'Key' => 'mailingState',
                'Value' => $member->getMailingState()
            ],
            [
                'Key' => 'mailingPostalCode',
                'Value' => $member->getMailingPostalCode()
            ],
            [
                'Key' => 'mailingCountry',
                'Value' => $member->getMailingCountry()
            ],
            [
                'Key' => 'employer',
                'Value' => $member->getEmployer()
            ],
            [
                'Key' => 'jobTitle',
                'Value' => $member->getJobTitle()
            ],
            [
                'Key' => 'occupation',
                'Value' => $member->getOccupation()
            ]
        ];
    }
}
