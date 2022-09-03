<?php

namespace App\Controller;

use App\Entity\DirectoryCollection;
use App\Entity\Member;
use App\Entity\Tag;
use App\Form\MapFilterType;
use App\Repository\DirectoryCollectionRepository;
use App\Repository\MemberRepository;
use App\Repository\TagRepository;
use App\Service\EmailService;
use App\Service\PostalValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/directory")
 */
class DirectoryController extends AbstractController
{
    public const COLUMN_MAP = [
        'm.localIdentifier',
        null,
        'm.lastName',
        's.label',
        'm.classYear',
        'm.primaryEmail',
        null,
        'm.primaryTelephoneNumber',
        null,
    ];

    /**
     * @Route("/", name="home")
     */
    public function index(DirectoryCollectionRepository $directoryCollectionRepository)
    {
        try {
            $records = $directoryCollectionRepository->getDefaultDirectoryCollection();

            return $this->redirectToRoute('directory_collection', ['slug' => $records->getSlug()]);
        } catch (NoResultException $e) {
            return $this->render('getting-started.html.twig');
        }
    }

    /**
     * @Route("/collection/{slug}.{_format}", name="directory_collection", defaults={"_format": "html"}), options={"expose" = true})
     */
    public function directoryCollectionTableSource(DirectoryCollection $directoryCollection, MemberRepository $memberRepository, Request $request, string $_format): Response
    {
        if ('html' === $_format) {
            return $this->render('directory/directory.html.twig', [
                'view_name' => $directoryCollection->getLabel(),
                'group_by' => $directoryCollection->getGroupBy(),
                'data_source' => $this->generateUrl('directory_collection', ['slug' => $directoryCollection->getSlug(), '_format' => 'json']),
                'show_status' => $directoryCollection->getShowMemberStatus(),
            ]);
        }

        $members = $memberRepository->findByDirectoryCollection($directoryCollection, [
            'limit' => $request->get('length', 100),
            'offset' => $request->get('start', 0),
            'group_by' => $this->getGroupBy($directoryCollection),
            'sort_by' => $this->getSortBy($request),
            'sort_direction' => $this->getSortDirection($request),
        ]);
        $response = $this->buildDataResponse($members);

        return $this->json($response, 200, [], [
            'groups' => ['member_main', 'member_extended', 'status_main', 'tag_main'],
            'circular_reference_handler' => function ($object) {
                return (string) $object;
            },
        ]);
    }

    /**
     * @Route("/lost.{_format}", name="lost", defaults={"_format": "html"}, options={"expose": true})
     */
    public function lost(MemberRepository $memberRepository, Request $request, string $_format)
    {
        if ('html' === $_format) {
            return $this->render('directory/directory.html.twig', [
                'view_name' => 'Lost',
                'show_status' => true,
                'data_source' => $this->generateUrl('lost', ['_format' => 'json']),
            ]);
        }
        $members = $memberRepository->findLost([
            'limit' => $request->get('length', 100),
            'offset' => $request->get('start', 0),
            'sort_by' => $this->getSortBy($request),
            'sort_direction' => $this->getSortDirection($request),
        ]);
        $response = $this->buildDataResponse($members);

        return $this->json($response, 200, [], [
            'groups' => ['member_main', 'member_extended', 'status_main', 'tag_main'],
            'circular_reference_handler' => function ($object) {
                return (string) $object;
            },
        ]);
    }

    /**
     * @Route("/do-not-contact.{_format}", name="do_not_contact", defaults={"_format": "html"}, options={"expose": true})
     */
    public function doNotContact(MemberRepository $memberRepository, Request $request, string $_format)
    {
        if ('html' === $_format) {
            return $this->render('directory/directory.html.twig', [
                'view_name' => 'Do Not Contact',
                'show_status' => true,
                'data_source' => $this->generateUrl('do_not_contact', ['_format' => 'json']),
            ]);
        }
        $members = $memberRepository->findDoNotContact([
            'limit' => $request->get('length', 100),
            'offset' => $request->get('start', 0),
            'sort_by' => $this->getSortBy($request),
            'sort_direction' => $this->getSortDirection($request),
        ]);
        $response = $this->buildDataResponse($members);

        return $this->json($response, 200, [], [
            'groups' => ['member_main', 'member_extended', 'status_main', 'tag_main'],
            'circular_reference_handler' => function ($object) {
                return (string) $object;
            },
        ]);
    }

    /**
     * @Route("/deceased.{_format}", name="deceased", defaults={"_format": "html"}, options={"expose": true})
     */
    public function deceased(MemberRepository $memberRepository, Request $request, string $_format)
    {
        if ('html' === $_format) {
            return $this->render('directory/directory.html.twig', [
                'view_name' => 'Deceased',
                'show_status' => true,
                'data_source' => $this->generateUrl('deceased', ['_format' => 'json']),
            ]);
        }
        $members = $memberRepository->findDeceased([
            'limit' => $request->get('length', 100),
            'offset' => $request->get('start', 0),
            'sort_by' => $this->getSortBy($request),
            'sort_direction' => $this->getSortDirection($request),
        ]);
        $response = $this->buildDataResponse($members);

        return $this->json($response, 200, [], [
            'groups' => ['member_main', 'member_extended', 'status_main', 'tag_main'],
            'circular_reference_handler' => function ($object) {
                return (string) $object;
            },
        ]);
    }

    /**
     * @Route("/verify-address-data", name="verify_address_data", options={"expose": true})
     * @IsGranted("ROLE_DIRECTORY_MANAGER")
     */
    public function validateMemberAddress(Request $request, PostalValidatorService $postalValidatorService): Response
    {
        if (!$postalValidatorService->isConfigured()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Postal validation service not configured',
            ], 500);
        }

        $member = new Member();
        $member->setMailingAddressLine1($request->query->get('mailingAddressLine1'));
        $member->setMailingAddressLine2($request->query->get('mailingAddressLine2'));
        $member->setMailingCity($request->query->get('mailingCity'));
        $member->setMailingState($request->query->get('mailingState'));
        $member->setMailingPostalCode($request->query->get('mailingPostalCode'));

        $cache = new FilesystemAdapter();
        $cacheKey = 'directory.usps_address_verify_'.md5(json_encode($request->query->all()));
        $item = $cache->getItem($cacheKey);
        if (!$item->isHit()) {
            $item->set($postalValidatorService->validate($member));
            $item->expiresAfter(300);
            $cache->save($item);
        }

        $jsonResponse = $item->get();

        if (isset($jsonResponse['AddressValidateResponse']['Address']['Error'])) {
            return $this->json([
                'status' => 'error',
                'message' => $jsonResponse['AddressValidateResponse']['Address']['Error']['Description'],
            ], 500);
        }

        if (!isset($jsonResponse['AddressValidateResponse']['Address'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid response from API.',
            ]);
        }

        return $this->json([
            'status' => 'success',
            'verify' => $jsonResponse['AddressValidateResponse']['Address'],
        ]);
    }

    /**
     * @Route("/recent-changes", name="member_changes")
     */
    public function recentChanges(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createFormBuilder([
            'since' => $request->get('since', new \DateTime(date('Y-m-d', strtotime('-30 day')))),
            'exclude_inactive' => $request->get('exclude_inactive', true),
        ])
            ->add('since', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('exclude_inactive', CheckboxType::class, [
                'label' => 'Exclude Inactive',
                'required' => false,
                'row_attr' => [
                    'class' => 'mb-0'
                ],
            ])
            ->getForm()
        ;

        $form->handleRequest($request);
        $data = $form->getData();
        $members = $entityManager->getRepository(Member::class)->findRecentUpdates($data, $this->getUser()->getTimezone());

        return $this->render('directory/recent_changes.html.twig', [
            'members' => $members,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tags/{tagId}.{_format}", name="tag", defaults={"_format": "html"}, options={"expose": true})
     */
    public function tag(MemberRepository $memberRepository, TagRepository $tagRepository, Request $request, $_format, $tagId)
    {
        $tag = $tagRepository->find($tagId);
        if (is_null($tag)) {
            throw $this->createNotFoundException('Tag not found.');
        }

        if ('html' === $_format) {
            return $this->render('directory/directory.html.twig', [
                'view_name' => $tag->getTagName(),
                'show_status' => true,
                'data_source' => $this->generateUrl('tag', ['tagId' => $tagId, '_format' => 'json']),
                'messenger' => [
                    'key' => 'tag_id',
                    'value' => $tag->getId(),
                ],
            ]);
        }
        $members = $memberRepository->findByTags([$tag], [
            'limit' => $request->get('length', 100),
            'offset' => $request->get('start', 0),
            'sort_by' => $this->getSortBy($request),
            'sort_direction' => $this->getSortDirection($request),
        ]);
        $response = $this->buildDataResponse($members);

        return $this->json($response, 200, [], [
            'groups' => ['member_main', 'member_extended', 'status_main', 'tag_main'],
            'circular_reference_handler' => function ($object) {
                return (string) $object;
            },
        ]);
    }

    /**
     * @Route("/map", name="map")
     */
    public function map(Request $request)
    {
        $form = $this->createForm(MapFilterType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        }

        return $this->render('directory/map.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/map-search", name="map_search", options={"expose": true})
     */
    public function mapSearch(MemberRepository $memberRepository, Request $request)
    {
        $memberStatuses = $request->get('member_statuses', []);
        $members = $memberRepository->findMembersWithinRadius(
            $request->get('latitude'),
            $request->get('longitude'),
            $request->get('radius'),
            ['member_statuses' => $memberStatuses]
        );

        return $this->json($members, 200, [], [
            'groups' => ['member_main', 'member_extended', 'status_main', 'tag_main'],
            'circular_reference_handler' => function ($object) {
                return (string) $object;
            },
        ]);
    }

    /**
     * @Route("/map-data", name="map_data", options={"expose": true})
     */
    public function mapData(MemberRepository $memberRepository, Request $request)
    {
        $memberStatuses = $request->get('member_statuses', []);
        $members = $memberRepository->findGeocodedAddresses(['member_statuses' => $memberStatuses]);

        return $this->json($members, 200, [], [
            'groups' => ['member_main', 'member_extended', 'status_main', 'tag_main'],
            'circular_reference_handler' => function ($object) {
                return (string) $object;
            },
        ]);
    }

    /**
     * @Route("/email-campaign/{campaignId}", name="email_campaign_view")
     */
    public function viewCampaign($campaignId, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');

            return $this->redirectToRoute('home');
        }
        $campaign = $emailService->getCampaignById($campaignId);

        return $this->redirect($campaign->WebVersionURL);
    }

    /**
     * @Route("/birthdays", name="birthdays")
     */
    public function birthdays(MemberRepository $memberRepository): Response
    {
        $birthdays = $memberRepository->findBirthdays();
        $birthdayMap = [];
        foreach ($birthdays as $birthday) {
            $member = $birthday[0];
            $month = $birthday['bdMonth'];
            $day = $birthday['bdDay'];
            $birthdayMap[$month][$day][] = $member;
        }

        return $this->render('directory/birthdays.html.twig', [
            'birthdays' => $birthdays,
            'birthdayMap' => $birthdayMap,
        ]);
    }

    /* Private Methods */

    private function getSortBy(Request $request): string
    {
        $order = $request->get('order');
        if (isset($order[0]['column'], self::COLUMN_MAP[(int) $order[0]['column']])) {
            return self::COLUMN_MAP[(int) $order[0]['column']];
        }

        return 'm.localIdentifier';
    }

    private function getSortDirection(Request $request): string
    {
        $order = $request->get('order');
        if (isset($order[0]['dir']) && 'desc' === $order[0]['dir']) {
            return 'DESC';
        }

        return 'ASC';
    }

    private function getGroupBy(DirectoryCollection $directoryCollection): ?string
    {
        switch ($directoryCollection->getGroupBy()) {
            case 'classYear':
                return 'm.classYear';
            case 'status':
                return 's.label';
            case 'mailingState':
                return 'm.mailingState';
            case 'mailingPostalCode':
                return 'm.mailingPostalCode';
        }

        return null;
    }

    private function buildDataResponse(Paginator $members): array
    {
        return [
            'recordsTotal' => count($members),
            'recordsFiltered' => count($members),
            'data' => $members,
        ];
    }
}
