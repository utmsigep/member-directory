<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Entity\Member;

class GeocoderService
{
    protected $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    const CENSUS_BASE_URL = 'https://geocoding.geo.census.gov/geocoder/locations/address';
    const ARCGIS_BASE_URL = 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find/';
    const BENCHMARK = 'Public_AR_Current';
    const RETURN_FORMAT = 'json';

    public function geocodeMemberMailingAddress(Member $member): Member
    {
        $jsonObject = $this->makeCensusRequest([
            'street' => join(' ', [$member->getMailingAddressLine1(), $member->getMailingAddressLine2()]),
            'city' => $member->getMailingCity(),
            'state' => $member->getMailingState(),
            'zip' => $member->getMailingPostalCode(),
            'benchmark' => self::BENCHMARK,
            'format' => self::RETURN_FORMAT
        ]);

        // Geocoded address on first try
        if (property_exists($jsonObject, 'result')) {
            $result = $jsonObject->result;
            if ($result->addressMatches && count($result->addressMatches) > 0) {
                $member->setMailingLatitude($result->addressMatches[0]->coordinates->y);
                $member->setMailingLongitude($result->addressMatches[0]->coordinates->x);
                return $member;
            }
        }

        // Retry with ARCGIS Zip Code lookup as a fallback
        $jsonObject = $this->makeArcGisRequest([
            'sourceCountry' => $member->getMailingCountry(),
            'text' => $member->getMailingPostalCode(),
            'maxLocations' => 1,
            'f' => 'json',
            'returnGeometry' => 'true'
        ]);
        if (property_exists($jsonObject, 'locations')) {
            $locations = $jsonObject->locations;
            if ($locations[0] && $locations[0]->feature->geometry) {
                $member->setMailingLatitude($locations[0]->feature->geometry->y);
                $member->setMailingLongitude($locations[0]->feature->geometry->x);
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

    private function makeCensusRequest($parameters = []): object
    {
        $response = $this->httpClient->request('GET', self::CENSUS_BASE_URL, [
            'query' => $parameters,
            'headers' => [
                'Accept' => 'application/json',
            ]
        ]);
        return json_decode($response->getContent());
    }

    private function makeArcGisRequest($parameters = []): object
    {
        $response = $this->httpClient->request('GET', self::ARCGIS_BASE_URL, [
            'query' => $parameters,
            'headers' => [
                'Accept' => 'application/json',
            ]
        ]);
        return json_decode($response->getContent());
    }

}
