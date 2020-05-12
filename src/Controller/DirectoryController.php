<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Tag;
use App\Service\EmailService;
use App\Service\PostalValidatorService;
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
    public function index()
    {
        return $this->redirectToRoute('alumni');
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
     * @Route("/alumni", name="alumni")
     */
    public function alumni(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'ALUMNUS',
            'RENAISSANCE'
        ]);
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Alumni',
            'show_status' => false,
            'members' => $members
        ]);
    }

    /**
     * @Route("/undergraduates", name="undergraduates")
     */
    public function undergraduates()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'UNDERGRADUATE'
        ]);
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Undergraduates',
            'show_status' => false,
            'members' => $members
        ]);
    }

    /**
     * @Route("/resigned-expelled", name="resigned_expelled")
     */
    public function resignedExpelled()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'RESIGNED',
            'EXPELLED'
        ]);
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Resigned/Expelled',
            'show_status' => true,
            'members' => $members
        ]);
    }

    /**
     * @Route("/lost", name="lost")
     */
    public function lost()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = $entityManager->getRepository(Member::class)->findLostByStatusCodes([
            'ALUMNUS',
            'RENAISSANCE'
        ]);
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Lost Alumni',
            'show_status' => false,
            'members' => $members
        ]);
    }

    /**
     * @Route("/do-not-contact", name="do_not_contact")
     */
    public function doNotContact(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = $entityManager->getRepository(Member::class)->findDoNotContactByStatusCodes(
            [
                'ALUMNUS',
                'RENAISSANCE'
            ],
            $request->query->get('type')
        );
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Do Not Contact',
            'show_status' => false,
            'members' => $members
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
        $members = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'TRANSFERRED'
        ]);
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Transferred',
            'show_status' => false,
            'members' => $members
        ]);
    }

    /**
     * @Route("/other", name="other")
     */
    public function other()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'OTHER'
        ]);
        return $this->render('directory/directory.html.twig', [
            'view_name' => 'Other',
            'show_status' => false,
            'members' => $members
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
            $request->get('radius'),
            [
                'UNDERGRADUATE',
                'ALUMNUS',
                'RENAISSANCE',
                'TRANSFERRED'
            ]
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
        $members = $entityManager->getRepository(Member::class)->findGeocodedAddresses([
            'UNDERGRADUATE',
            'ALUMNUS',
            'RENAISSANCE',
            'TRANSFERRED'
        ]);

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
            return $this->redirectToRoute('member', ['localIdentifier' => $localIdentifier]);
        }
        $campaign = $emailService->getCampaignById($campaignId);
        return $this->redirect($campaign->WebVersionURL);
    }
}
