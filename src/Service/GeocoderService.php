<?php

namespace App\Service;

use App\Entity\Member;
use Geocodio\Geocodio;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocoderService
{
    protected $params;
    protected $logger;
    protected $httpClient;
    protected $source;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger, HttpClientInterface $httpClient)
    {
        $this->params = $params;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    const CENSUS_BASE_URL = 'https://geocoding.geo.census.gov/geocoder/locations/address';
    const ARCGIS_BASE_URL = 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find/';
    const BENCHMARK = 'Public_AR_Current';
    const RETURN_FORMAT = 'json';

    public function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function geocodeMemberMailingAddress(Member $member): Member
    {
        $jsonObject = $this->makeGeocodioRequest([
            'street' => join(' ', [$member->getMailingAddressLine1(), $member->getMailingAddressLine2()]),
            'city' => $member->getMailingCity(),
            'state' => $member->getMailingState(),
            'postal_code' => $member->getMailingPostalCode(),
            'country' => $member->getMailingCountry()
        ]);
        if ($jsonObject && property_exists($jsonObject, 'results') && count($jsonObject->results) > 0) {
            $result = $jsonObject->results[0];
            $this->source = 'geocodio';
            $member->setMailingLatitude($result->location->lat);
            $member->setMailingLongitude($result->location->lng);
            return $member;
        }

        // Use census data if no result or error
        $jsonObject = $this->makeCensusRequest([
            'street' => join(' ', [$member->getMailingAddressLine1(), $member->getMailingAddressLine2()]),
            'city' => $member->getMailingCity(),
            'state' => $member->getMailingState(),
            'zip' => $member->getMailingPostalCode(),
            'benchmark' => self::BENCHMARK,
            'format' => self::RETURN_FORMAT
        ]);
        if (property_exists($jsonObject, 'result')) {
            $result = $jsonObject->result;
            if ($result->addressMatches && count($result->addressMatches) > 0) {
                $this->source = 'census';
                $member->setMailingLatitude($result->addressMatches[0]->coordinates->y);
                $member->setMailingLongitude($result->addressMatches[0]->coordinates->x);
                return $member;
            }
        }

        // Retry with ARCGIS Zip Code lookup as a final fallback
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
                $this->source = 'arcgis';
                $member->setMailingLatitude($locations[0]->feature->geometry->y);
                $member->setMailingLongitude($locations[0]->feature->geometry->x);
                return $member;
            }
        }

        // If request failed entirely
        if (property_exists($jsonObject, 'errors')) {
            $errors = $jsonObject->errors;
            if ($errors && count($errors) > 0) {
                $this->logger->error(join('|', $errors));
                throw new \Exception(join('|', $errors));
            }
        }
        return $member;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    private function makeGeocodioRequest($parameters = []): ?object
    {
        if (!$this->params->get('geocodio.api_key')) {
            return null;
        }
        $geocoder = new Geocodio();
        $geocoder->setApiKey($this->params->get('geocodio.api_key'));
        $response = $geocoder->geocode($parameters, [], 1);
        $this->logger->debug(json_encode($response));
        return $response;
    }

    private function makeCensusRequest($parameters = []): object
    {
        $response = $this->httpClient->request('GET', self::CENSUS_BASE_URL, [
            'query' => $parameters,
            'headers' => [
                'Accept' => 'application/json',
            ]
        ]);
        $this->logger->debug($response->getContent());
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
        $this->logger->debug($response->getContent());
        return json_decode($response->getContent());
    }
}
