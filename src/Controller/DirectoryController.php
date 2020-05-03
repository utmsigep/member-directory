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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mailer\MailerInterface;

use App\Service\PostalValidatorService;
use App\Service\EmailService;
use App\Service\MemberToCsvService;
use App\Entity\Member;
use App\Entity\Tag;
use App\Entity\Donation;
use App\Form\MemberMessageType;
use App\Form\MemberType;

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
     * @Route("/member/new", name="member_new", options={"expose" = true})
     * @IsGranted("ROLE_ADMIN")
     */
    public function memberNew(Request $request): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($member);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s created!', $member));
            return $this->redirect($this->generateUrl('member', [
                'localIdentifier' => $member->getLocalIdentifier()
            ]));
        }
        return $this->render('member/new.html.twig', [
            'record' => $member,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}", name="member", options={"expose" = true}))
     */
    public function show(Member $member): Response
    {
        return $this->render('member/show.html.twig', [
            'record' => $member
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/edit", name="member_edit", options={"expose" = true})
     * @IsGranted("ROLE_ADMIN")
     */
    public function memberEdit(Member $member, Request $request): Response
    {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($member);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s updated!', $member));
            return $this->redirect($this->generateUrl('member', [
                'localIdentifier' => $member->getLocalIdentifier()
            ]));
        }
        return $this->render('member/edit.html.twig', [
            'record' => $member,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/delete", name="member_delete", options={"expose" = true})
     * @IsGranted("ROLE_ADMIN")
     */
    public function memberDelete(Member $member, Request $request): Response
    {
        $form = $this->createFormBuilder($member)
            ->getForm()
            ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($member);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s deleted!', $member));
            return $this->redirect($this->generateUrl('home'));
        }
        return $this->render('member/delete.html.twig', [
            'record' => $member,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/change-log", name="member_change_log")
     * @IsGranted("ROLE_ADMIN")
     */
    public function changeLog(Member $member): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $logEntries = $entityManager->getRepository(LogEntry::class)->getLogEntries($member);
        return $this->render('directory/change_log.html.twig', [
            'record' => $member,
            'logEntries' => $logEntries
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/donations", name="member_donations")
     * @IsGranted("ROLE_ADMIN")
     */
    public function donations(Member $member): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $donations = $entityManager->getRepository(Donation::class)->findByMember($member);
        $totals = $entityManager->getRepository(Donation::class)->getTotalDonationsForMember($member);
        return $this->render('member/donations.html.twig', [
            'record' => $member,
            'donations' => $donations,
            'totals' => $totals
        ]);
    }

    /**
     * @Route("/verify-address-data", name="verify_address_data", options={"expose" = true})
     * @IsGranted("ROLE_ADMIN")
     */
    public function validateMemberAddress(Request $request, PostalValidatorService $postalValidatorService): Response
    {
        if (!$postalValidatorService->isConfigured()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Postal validation service not configured'
            ], 500);
        }

        $member = new Member();
        $member->setMailingAddressLine1($request->query->get('mailingAddressLine1'));
        $member->setMailingAddressLine2($request->query->get('mailingAddressLine2'));
        $member->setMailingCity($request->query->get('mailingCity'));
        $member->setMailingState($request->query->get('mailingState'));
        $member->setMailingPostalCode($request->query->get('mailingPostalCode'));

        $cache = new FilesystemAdapter();
        $cacheKey = 'directory.address_verify_' . md5(json_encode($request->query->all()));
        $response = $cache->getItem($cacheKey);
        if (!$response->isHit()) {
            $response->set($postalValidatorService->validate($member));
            $cache->save($response);
        }

        $jsonResponse = $response->get();

        if (isset($jsonResponse['AddressValidateResponse']['Address']['Error'])) {
            return $this->json([
                'status' => 'error',
                'message' => $jsonResponse['AddressValidateResponse']['Address']['Error']['Description']
            ], 500);
        }

        return $this->json([
            'status' => 'success',
            'verify' => $jsonResponse['AddressValidateResponse']['Address']
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/email-subscription", name="member_email_subscription")
     */
    public function emailSubscription(Member $member, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $subscriber = $emailService->getMemberSubscription($member);
        $subscriberHistory = $emailService->getMemberSubscriptionHistory($member);

        return $this->render('directory/email_subscription.html.twig', [
            'record' => $member,
            'subscriber' => $subscriber,
            'subscriberHistory' => $subscriberHistory
        ]);
    }

    /**
     * @Route("/email-campaign/{campaignId}", name="email_campaign_view")
     */
    public function viewCampaign($campaignId, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $localIdentifier]);
        }
        $campaign = $emailService->getCampaignById($campaignId);
        return $this->redirect($campaign->WebVersionURL);
    }

    /**
     * @Route("/member/{localIdentifier}/add-subscriber", name="member_email_subscribe")
     * @IsGranted("ROLE_ADMIN")
     */
    public function addSubscriber(Member $member, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        if ($emailService->subscribeMember($member, true)) {
            $this->addFlash('success', 'Subscriber record created!');
        } else {
            $this->addFlash('danger', 'Unable to subscribe user. Is the email address valid?');
        }
        return $this->redirectToRoute(
            'member_email_subscription',
            [
                'localIdentifier' => $member->getLocalIdentifier()
            ]
        );
    }

    /**
     * @Route("/member/{localIdentifier}/update-subscriber", name="member_email_update")
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateSubscriber(Member $member, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        if ($member->getPrimaryEmail() && $emailService->updateMember($member->getPrimaryEmail(), $member)) {
            $this->addFlash('success', 'Subscriber record updated!');
        } else {
            $this->addFlash('danger', 'Unable to update user.');
        }
        return $this->redirectToRoute(
            'member_email_subscription',
            [
                'localIdentifier' => $member->getLocalIdentifier()
            ]
        );
    }

    /**
     * @Route("/member/{localIdentifier}/remove-subscriber", name="member_email_remove")
     * @IsGranted("ROLE_ADMIN")
     */
    public function removeSubscriber(Member $member, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');
            return $this->redirectToRoute('member', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        if ($emailService->unsubscribeMember($member)) {
            $this->addFlash('success', 'Subscriber record removed!');
        } else {
            $this->addFlash('danger', 'Unable to unsubscribe user.');
        }
        return $this->redirectToRoute(
            'member_email_subscription',
            [
                'localIdentifier' => $member->getLocalIdentifier()
            ]
        );
    }

    /**
     * @Route("/member/{localIdentifier}/vcard", name="member_vcard")
     */
    public function generateVCard(Member $member): Response
    {
        // Create the VCard
        $vcard = new VCard();
        $vcard->addName($member->getLastName(), $member->getPreferredName());
        $vcard->addJobtitle($member->getJobTitle());
        $vcard->addCompany($member->getEmployer());
        $vcard->addEmail($member->getPrimaryEmail(), 'PREF;HOME');
        $vcard->addPhoneNumber($member->getPrimaryTelephoneNumber(), 'PREF;HOME;VOICE');
        $vcard->addAddress(
            '',
            $member->getMailingAddressLine2(),
            $member->getMailingAddressLine1(),
            $member->getMailingCity(),
            $member->getMailingState(),
            $member->getMailingPostalCode(),
            $member->getMailingCountry(),
            'HOME;POSTAL'
        );

        return new Response($vcard->getOutput(), 200, $vcard->getHeaders(true));
    }

    /**
     * @Route("/member/{localIdentifier}/message", name="member_message")
     */
    public function message(Member $member, Request $request, MailerInterface $mailer): Response
    {
        if (!$member->getPrimaryEmail()) {
            $this->addFlash('danger', 'No email set for member.');
            return $this->redirectToRoute('member', ['localIdentifier' => $member->getLocalIdentifier()]);
        }

        $form = $this->createForm(MemberMessageType::class, null, ['acting_user' => $this->getUser()]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formData = $form->getData();
            $headers = new Headers();
            $headers->addTextHeader('X-Cmail-GroupName', 'Member Message');
            $headers->addTextHeader('X-MC-Tags', 'Member Message');
            $message = new TemplatedEmail($headers);
            $message
                ->to($member->getPrimaryEmail())
                ->from($this->getParameter('app.email.from'))
                ->replyTo($formData['reply_to'] ? $formData['reply_to'] : $this->getParameter('app.email.to'))
                ->subject($formData['subject'])
                ->htmlTemplate('directory/message_email.html.twig')
                ->context([
                    'subject' => $this->formatMessage($formData['subject'], $member),
                    'body' => $this->formatMessage($formData['message_body'], $member)
                ])
                ;
            if ($formData['send_copy']) {
                $message->bcc($this->getUser()->getEmail());
            }
            $mailer->send($message);
            $this->addFlash('success', 'Message sent!');
        }

        return $this->render('directory/message.html.twig', [
            'record' => $member,
            'form' => $form->createView()
        ]);
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
     * @Route("/do-not-contact", name="do_not_contact")
     */
    public function doNotContact(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findDoNotContactByStatusCodes(
            [
                'ALUMNUS',
                'RENAISSANCE'
            ],
            $request->query->get('type')
        );
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Do Not Contact',
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
        return $this->render('directory/directory_group.html.twig', [
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

        return $this->render('directory/recent_changes.html.twig', [
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
        $members = $entityManager->getRepository(Member::class)->findByTags($tag);
        return $this->render('directory/directory.html.twig', [
            'view_name' => $tag->getTagName(),
            'show_status' => true,
            'records' => $members
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
                'RENAISSANCE',
                'TRANSFERRED'
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
            'RENAISSANCE',
            'TRANSFERRED'
        ]);

        $jsonObject = $serializer->serialize($records, 'json', [
            'ignored_attributes' => ['status' => 'members'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);
    }

    private function formatMessage($content, Member $member): string
    {
        $content = preg_replace('/\[FirstName\]/i', $member->getFirstName(), $content);
        $content = preg_replace('/\[MiddleName\]/i', $member->getMiddleName(), $content);
        $content = preg_replace('/\[PreferredName\]/i', $member->getPreferredName(), $content);
        $content = preg_replace('/\[LastName\]/i', $member->getLastName(), $content);
        $content = preg_replace('/\[MailingAddressLine1\]/i', $member->getMailingAddressLine1(), $content);
        $content = preg_replace('/\[MailingAddressLine2\]/i', $member->getMailingAddressLine2(), $content);
        $content = preg_replace('/\[MailingCity\]/i', $member->getMailingCity(), $content);
        $content = preg_replace('/\[MailingState\]/i', $member->getMailingState(), $content);
        $content = preg_replace('/\[MailingPostalCode\]/i', $member->getMailingPostalCode(), $content);
        $content = preg_replace('/\[MailingCountry\]/', $member->getMailingCountry(), $content);
        $content = preg_replace('/\[PrimaryEmail\]/i', $member->getPrimaryEmail(), $content);
        $content = preg_replace('/\[PrimaryTelephoneNumber\]/', $member->getPrimaryTelephoneNumber(), $content);

        return $content;
    }
}
