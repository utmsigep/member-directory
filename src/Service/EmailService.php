<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use CS_REST_Subscribers;
use CS_REST_Campaigns;

use App\Entity\Member;

class EmailService
{
    protected $params;

    protected $apiKey;

    protected $defaultListId;

    protected $client;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->apiKey = $params->get('campaign_monitor.api_key');
        $this->defaultListId = $params->get('campaign_monitor.default_list_id');
        $this->client = new CS_REST_Subscribers(
            $this->defaultListId,
            [
                'api_key' => $this->apiKey
            ]
        );
        $this->campaignsClient = new CS_REST_Campaigns(
            $this->defaultListId,
            [
                'api_key' => $this->apiKey
            ]
        );
    }

    public function isConfigured(): bool
    {
        if ($this->params->get('campaign_monitor.api_key') && $this->params->get('campaign_monitor.default_list_id')) {
            return true;
        }
        return false;
    }

    public function getMemberSubscription(Member $member)
    {
        if (!$member->getPrimaryEmail()) {
            return [];
        }
        $result = $this->client->get($member->getPrimaryEmail(), true);
        return $result->response;
    }

    public function getMemberSubscriptionHistory(Member $member)
    {
        if (!$member->getPrimaryEmail()) {
            return [];
        }
        $result = $this->client->get_history($member->getPrimaryEmail());
        return $result->response;
    }

    public function subscribeMember(Member $member, $resubscribe = false): bool
    {
        if (!$member->getPrimaryEmail()
            || $member->getIsLocalDoNotContact()
            || $member->getStatus()->getIsInactive()
        ) {
            return false;
        }
        $result = $this->client->add([
            'EmailAddress' => $member->getPrimaryEmail(),
            'Name' => $member->getDisplayName(),
            'CustomFields' => $this->buildCustomFieldArray($member),
            'ConsentToTrack' => 'yes',
            'Resubscribe' => $resubscribe
        ]);
        if ($result->was_successful()) {
            return true;
        }
        error_log(json_encode($result->response));
        return false;
    }

    public function updateMember(string $existingEmail, Member $member): bool
    {
        if (!$member->getPrimaryEmail()) {
            return false;
        }
        $result = $this->client->update($existingEmail, [
            'EmailAddress' => $member->getPrimaryEmail(),
            'Name' => $member->getDisplayName(),
            'CustomFields' => $this->buildCustomFieldArray($member),
            'ConsentToTrack' => 'yes'
        ]);
        if ($result->was_successful()) {
            return true;
        }
        error_log(json_encode($result->response));
        return false;
    }

    public function unsubscribeMember(Member $member): bool
    {
        if (!$member->getPrimaryEmail()) {
            return false;
        }
        $result = $this->client->unsubscribe($member->getPrimaryEmail());
        if ($result->was_successful()) {
            return true;
        }
        error_log(json_encode($result->response));
        return false;
    }

    public function deleteMember(Member $member): bool
    {
        if (!$member->getPrimaryEmail()) {
            return false;
        }
        $result = $this->client->delete($member->getPrimaryEmail());
        if ($result->was_successful()) {
            return true;
        }
        error_log(json_encode($result->response));
        return false;
    }

    public function getCampaignById($campaignId): object
    {
        $this->campaignsClient->set_campaign_id($campaignId);
        $result = $this->campaignsClient->get_summary();
        return $result->response;
    }

    /* Private Methods */

    private function buildCustomFieldArray(Member $member): array
    {
        return [
            [
                'Key' => 'Member Status',
                'Value' => $member->getStatus()->getLabel()
            ],
            [
                'Key' => 'First Name',
                'Value' => $member->getFirstName()
            ],
            [
                'Key' => 'Preferred Name',
                'Value' => $member->getPreferredName()
            ],
            [
                'Key' => 'Middle Name',
                'Value' => $member->getMiddleName()
            ],
            [
                'Key' => 'Last Name',
                'Value' => $member->getLastName()
            ],
            [
                'Key' => 'Class Year',
                'Value' => $member->getClassYear()
            ],
            [
                'Key' => 'Local Identifier',
                'Value' => $member->getLocalIdentifier()
            ],
            [
                'Key' => 'Local Identifier Short',
                'Value' => $member->getLocalIdentifierShort()
            ],
            [
                'Key' => 'External Identifier',
                'Value' => $member->getExternalIdentifier()
            ],
            [
                'Key' => 'Primary Telephone Number',
                'Value' => $member->getPrimaryTelephoneNumber()
            ],
            [
                'Key' => 'Mailing Address Line 1',
                'Value' => $member->getMailingAddressLine1()
            ],
            [
                'Key' => 'Mailing Address Line 2',
                'Value' => $member->getMailingAddressLine2()
            ],
            [
                'Key' => 'Mailing City',
                'Value' => $member->getMailingCity()
            ],
            [
                'Key' => 'Mailing State',
                'Value' => $member->getMailingState()
            ],
            [
                'Key' => 'Mailing Postal Code',
                'Value' => $member->getMailingPostalCode()
            ],
            [
                'Key' => 'Mailing Country',
                'Value' => $member->getMailingCountry()
            ],
            [
                'Key' => 'Employer',
                'Value' => $member->getEmployer()
            ],
            [
                'Key' => 'Job Title',
                'Value' => $member->getJobTitle()
            ],
            [
                'Key' => 'Occupation',
                'Value' => $member->getOccupation()
            ],
            [
                'Key' => 'LinkedIn Profile',
                'Value' => $member->getLinkedinUrl()
            ],
            [
                'Key' => 'Facebook Profile',
                'Value' => $member->getFacebookUrl()
            ],
            [
                'Key' => 'Tags',
                'Value' => $member->getTagsAsCSV()
            ],
            [
                'Key' => 'Update Token',
                'Value' => $member->getUpdateToken()
            ]
        ];
    }
}
