<?php

namespace App\Controller;

use Gedmo\Loggable\Entity\LogEntry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Routing\Annotation\Route;
use JeroenDesloovere\VCard\VCard;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Service\PostalValidatorService;
use App\Service\EmailService;
use App\Entity\Member;
use App\Entity\Tag;

/**
 * @IsGranted("ROLE_USER")
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
    public function member($localIdentifier): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        if (is_null($record)) {
            throw $this->createNotFoundException('Member not found.');
        }
        return $this->render('directory/member.html.twig', [
            'record' => $record
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/change-log", name="member_change_log")
     */
    public function changeLog($localIdentifier): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        if (is_null($record)) {
            throw $this->createNotFoundException('Member not found.');
        }
        $logEntries = $entityManager->getRepository(LogEntry::class)->getLogEntries($record);
        return $this->render('directory/change-log.html.twig', [
            'record' => $record,
            'logEntries' => $logEntries
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/verify-address", name="member_verify_address")
     */
    public function validateMemberAddress($localIdentifier, PostalValidatorService $postalValidatorService): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        if (is_null($record)) {
            throw $this->createNotFoundException('Member not found.');
        }
        $cache = new FilesystemAdapter();
        $cacheKey = 'directory.address_verify_' . md5(json_encode([$record->getId(), $record->getUpdatedAt()]));
        $response = $cache->getItem($cacheKey);

        if (!$response->isHit()) {
            $response->set($postalValidatorService->validate($record));
            $cache->save($response);
        }

        return $this->render('directory/verify-address.html.twig', [
            'record' => $record,
            'verify' => $response->get()['AddressValidateResponse']['Address']
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/email-subscription", name="member_email_subscription")
     */
    public function emailSubscription($localIdentifier): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        if (is_null($record)) {
            throw $this->createNotFoundException('Member not found.');
        }
        $emailService = new EmailService();
        $subscriber = $emailService->getMemberSubscription($record);
        $subscriberHistory = $emailService->getMemberSubscriptionHistory($record);

        return $this->render('directory/email-subscription.html.twig', [
            'record' => $record,
            'subscriber' => $subscriber,
            'subscriberHistory' => $subscriberHistory
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/add-subscriber", name="member_email_subscribe")
     * @IsGranted("ROLE_ADMIN")
     */
    public function addSubscriber($localIdentifier, EmailService $emailService): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        if (is_null($record)) {
            throw $this->createNotFoundException('Member not found.');
        }
        if ($emailService->subscribeMember($record, true)) {
            $this->addFlash('success', 'Subscriber record created!');
        } else {
            $this->addFlash('danger', 'Unable to subscribe user. Is the email address valid?');
        }
        return $this->redirectToRoute('member_email_subscription', ['localIdentifier' => $localIdentifier]);
    }

    /**
     * @Route("/member/{localIdentifier}/update-subscriber", name="member_email_update")
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateSubscriber($localIdentifier, EmailService $emailService): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        if (is_null($record)) {
            throw $this->createNotFoundException('Member not found.');
        }
        if ($record->getPrimaryEmail() && $emailService->updateMember($record->getPrimaryEmail(), $record)) {
            $this->addFlash('success', 'Subscriber record updated!');
        } else {
            $this->addFlash('danger', 'Unable to update user.');
        }
        return $this->redirectToRoute('member_email_subscription', ['localIdentifier' => $localIdentifier]);
    }

    /**
     * @Route("/member/{localIdentifier}/remove-subscriber", name="member_email_remove")
     * @IsGranted("ROLE_ADMIN")
     */
    public function removeSubscriber($localIdentifier, EmailService $emailService): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        if (is_null($record)) {
            throw $this->createNotFoundException('Member not found.');
        }
        if ($emailService->unsubscribeMember($record)) {
            $this->addFlash('success', 'Subscriber record removed!');
        } else {
            $this->addFlash('danger', 'Unable to unsubscribe user.');
        }
        return $this->redirectToRoute('member_email_subscription', ['localIdentifier' => $localIdentifier]);
    }

    /**
     * @Route("/member/{localIdentifier}/vcard", name="member_vcard")
     */
    public function generateVCard($localIdentifier): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        if (is_null($record)) {
            throw $this->createNotFoundException('Member not found.');
        }

        // Create the VCard
        $vcard = new VCard();
        $vcard->addName($record->getLastName(), $record->getPreferredName());
        $vcard->addJobtitle($record->getJobTitle());
        $vcard->addCompany($record->getEmployer());
        $vcard->addEmail($record->getPrimaryEmail(), 'PREF;HOME');
        $vcard->addPhoneNumber($record->getPrimaryTelephoneNumber(), 'PREF;HOME;VOICE');
        $vcard->addAddress(
            '',
            $record->getMailingAddressLine2(),
            $record->getMailingAddressLine1(),
            $record->getMailingCity(),
            $record->getMailingState(),
            $record->getMailingPostalCode(),
            $record->getMailingCountry(),
            'HOME;POSTAL'
        );

        return new Response($vcard->getOutput(), 200, $vcard->getHeaders(true));
    }

    /**
     * @Route("/alumni", name="alumni")
     */
    public function alumni(): Response
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
     * @Route("/tags/{tagId}", name="tag")
     */
    public function tag($tagId)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tag = $entityManager->getRepository(Tag::class)->find($tagId);
        if (is_null($tag)) {
            throw $this->createNotFoundException('Tag not found.');
        }
        return $this->render('directory/tag.html.twig', [
            'tag' => $tag,
            'records' => $tag->getMembers(),
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
