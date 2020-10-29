<?php

namespace App\Controller;

use App\Entity\DirectoryCollection;
use App\Entity\Member;
use App\Entity\Tag;
use App\Repository\DirectoryCollectionRepository;
use App\Repository\MemberRepository;
use App\Service\EmailService;
use App\Service\PostalValidatorService;
use Doctrine\ORM\NoResultException ;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/directory")
 */
class DirectoryController extends AbstractController
{
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
     * @Route("/collection/{slug}", name="directory_collection", options={"expose" = true})
     */
    public function directoryCollection(DirectoryCollection $directoryCollection, MemberRepository $memberRepository)
    {
        if ($directoryCollection->getGroupBy()) {
            return $this->render('directory/directory_group.html.twig', [
                'view_name' => $directoryCollection->getLabel(),
                'show_status' => $directoryCollection->getShowMemberStatus(),
                'group' => $memberRepository->findByDirectoryCollection($directoryCollection)
            ]);
        }
        return $this->render('directory/directory.html.twig', [
            'view_name' => $directoryCollection->getLabel(),
            'show_status' => $directoryCollection->getShowMemberStatus(),
            'members' => $memberRepository->findByDirectoryCollection($directoryCollection)
        ]);
    }

    /**
     * @Route("/lost", name="lost")
     */
    public function lost()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = $entityManager->getRepository(Member::class)->findLost();
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Lost',
            'show_status' => true,
            'members' => $members
        ]);
    }

    /**
     * @Route("/do-not-contact", name="do_not_contact")
     */
    public function doNotContact()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = $entityManager->getRepository(Member::class)->findDoNotContact();
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Do Not Contact',
            'show_status' => true,
            'members' => $members
        ]);
    }

    /**
     * @Route("/deceased", name="deceased")
     */
    public function deceased()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = $entityManager->getRepository(Member::class)->findDeceased();
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Do Not Contact',
            'show_status' => true,
            'members' => $members
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
                'widget' => 'single_text',
            ])
            ->add('exclude_inactive', CheckboxType::class, [
                'label' => 'Exclude Inactive',
                'required' => false,
            ])
            ->getForm()
            ;

        $form->handleRequest($request);
        $data = $form->getData();
        $members = $entityManager->getRepository(Member::class)->findRecentUpdates($data);

        return $this->render('directory/recent_changes.html.twig', [
            'members' => $members,
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
            'members' => $members
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
        $members = $entityManager->getRepository(Member::class)->findMembersWithinRadius(
            $request->get('latitude'),
            $request->get('longitude'),
            $request->get('radius')
        );

        $jsonObject = $serializer->serialize($members, 'json', [
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
        $members = $entityManager->getRepository(Member::class)->findGeocodedAddresses();

        $jsonObject = $serializer->serialize($members, 'json', [
            'ignored_attributes' => ['status' => 'members'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);
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
}
