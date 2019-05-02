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
        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'query' => $parameters,
            'headers' => [
                'Accept' => 'application/json',
            ]
        ]);
        $jsonObject = json_decode($response->getBody());
        if (property_exists($jsonObject, 'result')) {
            $result = $jsonObject->result;
            if ($result->addressMatches && count($result->addressMatches) > 0) {
                $member->setMailingLatitude($result->addressMatches[0]->coordinates->y);
                $member->setMailingLongitude($result->addressMatches[0]->coordinates->x);
            }
        }
        if (property_exists($jsonObject, 'errors')) {
            $errors = $jsonObject->errors;
            if ($errors && count($errors) > 0) {
                throw new \Exception(join('|', $errors));
            }
        }
        return $member;
    }
}
