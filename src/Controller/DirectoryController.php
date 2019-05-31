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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Date;

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
        if (!$postalValidatorService->isConfigured()) {
            $this->addFlash('danger', 'Mailing validation service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $localIdentifier]);
        }
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
    public function emailSubscription($localIdentifier, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $localIdentifier]);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        if (is_null($record)) {
            throw $this->createNotFoundException('Member not found.');
        }
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
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $localIdentifier]);
        }
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
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $localIdentifier]);
        }
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
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $localIdentifier]);
        }
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
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Alumni',
            'show_status' => false,
            'records' => $records
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
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Undergraduates',
            'show_status' => false,
            'records' => $records
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
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Resigned/Expelled',
            'show_status' => true,
            'records' => $records
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
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Lost Alumni',
            'show_status' => false,
            'records' => $records
        ]);
    }

    /**
     * @Route("/year", name="year")
     */
    public function year()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $group = $entityManager->getRepository(Member::class)->findByStatusCodesGroupByClassYear(
            [
                'ALUMNUS',
                'RENAISSANCE'
            ]
        );
        return $this->render('directory/directory-group.html.twig', [
            'view_name' => 'Class Year',
            'show_status' => false,
            'group' => $group
        ]);
    }


    /**
     * @Route("/transferred", name="transferred")
     */
    public function transferred()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'TRANSFERRED'
        ]);
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Transferred',
            'show_status' => false,
            'records' => $records
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
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Other',
            'show_status' => false,
            'records' => $records
        ]);
    }

    /**
     * @Route("/facebook", name="facebook")
     */
    public function facebook()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'ALUMNUS',
            'UNDERGRADUATE',
            'RENAISSANCE'
        ]);
        return $this->render('directory/facebook.html.twig', [
            'records' => $records
        ]);
    }

    /**
     * @Route("/recent-changes", name="member_changes")
     */
    public function recentChanges(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder([
            'since' => $request->get('since', new \DateTime(date('Y-m-d', strtotime('-30 day')))),
            'exclude_inactive' => $request->get('exclude_inactive', true)
        ])
            ->add('since', DateType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control mr-sm-2',
                ],
                'widget' => 'single_text',
            ])
            ->add('exclude_inactive', CheckboxType::class, [
                'label' => 'Exclude Inactive',
                'required' => false,
                'label_attr' => [
                    'class' => 'mr-sm-2',
                ],
            ])
            ->getForm()
            ;

        $form->handleRequest($request);
        $data = $form->getData();
        $records = $entityManager->getRepository(Member::class)->findRecentUpdates($data);

        return $this->render('directory/recent-changes.html.twig', [
            'records' => $records,
            'form' => $form->createView()
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
        return $this->render('directory/directory.html.twig', [
            'view_name' => $tag->getTagName(),
            'show_status' => true,
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
