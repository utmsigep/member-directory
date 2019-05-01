<?php

namespace App\Controller;

use Gedmo\Loggable\Entity\LogEntry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Routing\Annotation\Route;
use USPS\Address;
use USPS\AddressVerify;

use App\Entity\Member;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/directory")
 */
class DirectoryController extends AbstractController
{
    public function index()
    {
        return $this->redirectToRoute('alumni');
    }

    /**
     * @Route("/member/{localIdentifier}", name="member")
     */
    public function member($localIdentifier)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        return $this->render('directory/member.html.twig', [
            'record' => $record
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/change-log", name="member_change_log")
     */
    public function changeLog($localIdentifier)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        $logEntries = $entityManager->getRepository(LogEntry::class)->getLogEntries($record);
        return $this->render('directory/change-log.html.twig', [
            'record' => $record,
            'logEntries' => $logEntries
        ]);
    }


    /**
     * @Route("/member/{localIdentifier}/verify-address", name="member_verify_address")
     */
    public function validateMemberAddress($localIdentifier)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);

        $cache = new FilesystemAdapter();
        $cacheKey = 'directory.address_verify_' . md5(json_encode([$record->getId(), $record->getUpdatedAt()]));
        $response = $cache->getItem($cacheKey);

        if (!$response->isHit()) {
            $verify = new AddressVerify($_ENV['USPS_USERNAME']);
            $address = new Address();
            $address->setField('Address1', $record->getMailingAddressLine1());
            $address->setField('Address2', $record->getMailingAddressLine2());
            $address->setCity($record->getMailingCity());
            $address->setState($record->getMailingState());
            $address->setZip5($record->getMailingPostalCode());
            $address->setZip4('');
            $verify->addAddress($address);
            $verify->verify();

            $response->set($verify->getArrayResponse());
            $cache->save($response);
        }

        return $this->render('directory/verify-address.html.twig', [
            'record' => $record,
            'verify' => $response->get()['AddressValidateResponse']['Address']
        ]);
    }

    /**
     * @Route("/alumni", name="alumni")
     * @Route("/", name="home")
     */
    public function alumni()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'ALUMNUS',
            'RENAISSANCE'
        ]);
        return $this->render('directory/alumni.html.twig', [
            'records' => $records,
        ]);
    }

    /**
     * @Route("/undergraduates", name="undergraduates")
     */
    public function undergraduates()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'UNDERGRADUATE'
        ]);
        return $this->render('directory/undergraduates.html.twig', [
            'records' => $records,
        ]);
    }

    /**
     * @Route("/map", name="map")
     */
    public function map()
    {
        return $this->render('directory/map.html.twig');
    }

    /**
     * @Route("/map-data", name="map_data")
     */
    public function mapData()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findGeocodedAddresses([
            'UNDERGRADUATE',
            'ALUMNUS',
            'RENAISSANCE'
        ]);
        $output = [];
        foreach ($records as $record) {
            $output[] = [
                'localIdentifier' => $record->getLocalIdentifier(),
                'preferredName' => $record->getPreferredName(),
                'lastName' => $record->getLastName(),
                'mailingAddressLine1' => $record->getMailingAddressLine1(),
                'mailingAddressLine2' => $record->getMailingAddressLine2(),
                'mailingCity' => $record->getMailingCity(),
                'mailingState' => $record->getMailingState(),
                'mailingcountry' => $record->getMailingCountry(),
                'mailingPostalCode' => $record->getMailingPostalCode(),
                'mailingLatitude' => $record->getMailingLatitude(),
                'mailingLongitude' => $record->getMailingLongitude(),
                'status' => $record->getStatus()->getLabel()
            ];
        }
        return $this->json($output);
    }
}
