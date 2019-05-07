<?php

namespace App\Controller;

use Gedmo\Loggable\Entity\LogEntry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Routing\Annotation\Route;
use USPS\Address;
use USPS\AddressVerify;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Member;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/directory")
 */
class DirectoryController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->redirectToRoute('alumni');
    }

    /**
     * @Route("/member/{localIdentifier}", name="member", options={"expose" = true})
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
     * @Route("/resigned-expelled", name="resigned_expelled")
     */
    public function resignedExpelled()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'RESIGNED',
            'EXPELLED'
        ]);
        return $this->render('directory/resigned-expelled.html.twig', [
            'records' => $records,
        ]);
    }

    /**
     * @Route("/lost", name="lost")
     */
    public function lost()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findLostByStatusCodes([
            'ALUMNUS',
            'RENAISSANCE'
        ]);
        return $this->render('directory/lost.html.twig', [
            'records' => $records,
        ]);
    }

    /**
     * @Route("/other", name="other")
     */
    public function other()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'OTHER'
        ]);
        return $this->render('directory/other.html.twig', [
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
     * @Route("/map-search", name="map_search", options={"expose" = true})
     */
    public function mapSearch(Request $request, SerializerInterface $serializer)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findMembersWithinRadius(
            $request->get('latitude'),
            $request->get('longitude'),
            $request->get('radius'),
            [
                'UNDERGRADUATE',
                'ALUMNUS',
                'RENAISSANCE'
            ]
        );

        $jsonObject = $serializer->serialize($records, 'json', [
            'ignored_attributes' => ['status' => 'members'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/map-data", name="map_data", options={"expose" = true})
     */
    public function mapData(SerializerInterface $serializer)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findGeocodedAddresses([
            'UNDERGRADUATE',
            'ALUMNUS',
            'RENAISSANCE'
        ]);

        $jsonObject = $serializer->serialize($records, 'json', [
            'ignored_attributes' => ['status' => 'members'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);
    }
}
