<?php

namespace App\Service;

use App\Entity\Member;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PostalValidatorService
{
    protected string $uspsAPIBaseUrl = 'https://apis.usps.com/addresses/v3';
    protected ParameterBagInterface $params;
    protected HttpClientInterface $httpClient;

    public function __construct(ParameterBagInterface $params, HttpClientInterface $httpClient)
    {
        $this->params = $params;
        $this->httpClient = $httpClient;
    }

    public function isConfigured(): bool
    {
        try {
            return '' !== trim((string) $this->params->get('usps.auth_token'));
        } catch (\Throwable) {
            return false;
        }
    }

    public function validate(Member $member): array
    {
        $query = $this->buildAddressQuery($member);

        if (!isset($query['streetAddress']) || !isset($query['state']) || (!isset($query['city']) && !isset($query['ZIPCode']))) {
            return [
                'error' => [
                    'code' => '400',
                    'message' => 'Street address, state, and either city or ZIP Code are required.',
                ],
            ];
        }

        try {
            $response = $this->httpClient->request('GET', $this->uspsAPIBaseUrl.'/address', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->params->get('usps.auth_token'),
                    'Accept' => 'application/json',
                ],
                'query' => $query,
            ]);

            $payload = $response->toArray(false);

            if (200 !== $response->getStatusCode()) {
                return $payload + [
                    'error' => [
                        'code' => (string) $response->getStatusCode(),
                        'message' => $payload['error']['message'] ?? 'USPS address validation failed.',
                    ],
                ];
            }

            if (!isset($payload['address']) || !is_array($payload['address'])) {
                return [
                    'error' => [
                        'code' => '500',
                        'message' => 'Invalid response from USPS address validation API.',
                    ],
                ];
            }

            return $payload;
        } catch (ExceptionInterface $exception) {
            return [
                'error' => [
                    'code' => '500',
                    'message' => $exception->getMessage(),
                ],
            ];
        }
    }

    private function buildAddressQuery(Member $member): array
    {
        $query = [];

        $streetAddress = trim((string) $member->getMailingAddressLine1());
        if ('' !== $streetAddress) {
            $query['streetAddress'] = $streetAddress;
        }

        $secondaryAddress = trim((string) $member->getMailingAddressLine2());
        if ('' !== $secondaryAddress) {
            $query['secondaryAddress'] = $secondaryAddress;
        }

        $city = trim((string) $member->getMailingCity());
        if ('' !== $city) {
            $query['city'] = $city;
        }

        $state = strtoupper(trim((string) $member->getMailingState()));
        if ('' !== $state) {
            $query['state'] = $state;
        }

        $postalCode = trim((string) $member->getMailingPostalCode());
        if ('' !== $postalCode) {
            $postalCodeDigits = preg_replace('/\D/', '', $postalCode);
            if (strlen($postalCodeDigits) >= 5) {
                $query['ZIPCode'] = substr($postalCodeDigits, 0, 5);
            }

            if (strlen($postalCodeDigits) >= 9) {
                $query['ZIPPlus4'] = substr($postalCodeDigits, 5, 4);
            }
        }

        return $query;
    }
}
