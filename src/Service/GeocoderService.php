<?php

namespace App\Service;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use App\Entity\Member;

class GeocoderService
{
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient();
    }

    const BASE_URL = 'https://geocoding.geo.census.gov/geocoder/locations/address';
    const BENCHMARK = 'Public_AR_Current';
    const RETURN_FORMAT = 'json';

    public function geocodeMemberMailingAddress(Member $member): Member
    {
        $parameters = [
            'street' => join(' ', [$member->getMailingAddressLine1(), $member->getMailingAddressLine2()]),
            'city' => $member->getMailingCity(),
            'state' => $member->getMailingState(),
            'zip' => $member->getMailingPostalCode(),
            'benchmark' => self::BENCHMARK,
            'format' => self::RETURN_FORMAT
        ];

        $jsonObject = $this->makeRequest($parameters);

        // Geocoded address on first try
        if (property_exists($jsonObject, 'result')) {
            $result = $jsonObject->result;
            if ($result->addressMatches && count($result->addressMatches) > 0) {
                $member->setMailingLatitude($result->addressMatches[0]->coordinates->y);
                $member->setMailingLongitude($result->addressMatches[0]->coordinates->x);
                return $member;
            }
        }

        // Retry with '100 Main St' address and no zip code
        $parameters['street'] = '100 Main St';
        $parameters['zip'] = null;
        $jsonObject = $this->makeRequest($parameters);
        if (property_exists($jsonObject, 'result')) {
            $result = $jsonObject->result;
            if ($result->addressMatches && count($result->addressMatches) > 0) {
                $member->setMailingLatitude($result->addressMatches[0]->coordinates->y);
                $member->setMailingLongitude($result->addressMatches[0]->coordinates->x);
                return $member;
            }
        }

        // Retry with '100 2nd Ave' address and no zip code
        $parameters['street'] = '100 2nd Ave';
        $parameters['zip'] = null;
        $jsonObject = $this->makeRequest($parameters);
        if (property_exists($jsonObject, 'result')) {
            $result = $jsonObject->result;
            if ($result->addressMatches && count($result->addressMatches) > 0) {
                $member->setMailingLatitude($result->addressMatches[0]->coordinates->y);
                $member->setMailingLongitude($result->addressMatches[0]->coordinates->x);
                return $member;
            }
        }

        // If request failed entirely
        if (property_exists($jsonObject, 'errors')) {
            $errors = $jsonObject->errors;
            if ($errors && count($errors) > 0) {
                throw new \Exception(join('|', $errors));
            }
        }
        return $member;
    }

    private function makeRequest($parameters = []): object
    {
        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'query' => $parameters,
            'headers' => [
                'Accept' => 'application/json',
            ]
        ]);
        return json_decode($response->getBody());
    }
}
