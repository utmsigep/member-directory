<?php

namespace App\Controller;

use Gedmo\Loggable\Entity\LogEntry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;

use App\Form\MemberExportType;
use App\Service\MemberToCsvService;
use App\Entity\Member;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/directory/export")
 */
class ExportController extends AbstractController
{
    /**
     * @Route("/", name="export")
     */
    public function export(Request $request, MemberToCsvService $memberToCsvService)
    {
        $form = $this->createForm(MemberExportType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $filters = $form->getData();
            $members = $this->getDoctrine()->getRepository(Member::class)->findWithExportFilters($filters);

            $filename = 'member-export-' . date('Y-m-d') . '.csv';
            $response = new Response($memberToCsvService->arrayToCsvString(new ArrayCollection($members)));
            $response->headers->set('Content-type', 'text/csv');
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );
            $response->headers->set('Content-disposition', $disposition);
            return $response;
        }

        return $this->render('member/export.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/by-location", name="export_by_location", options={"expose" = true})
     */
    public function exportByLocation(Request $request, MemberToCsvService $memberToCsvService)
    {
        if (!$request->get('latitude') || !$request->get('longitude') || !$request->get('radius')) {
            throw new BadRequestHttpException('Invalid search parameters.');
        }

        $results = $this->getDoctrine()->getRepository(Member::class)->findMembersWithinRadius(
            (float) $request->get('latitude'),
            (float) $request->get('longitude'),
            (int) $request->get('radius'),
            [
                'UNDERGRADUATE',
                'ALUMNUS',
                'RENAISSANCE',
                'TRANSFERRED'
            ]
        );

        $members = [];
        foreach ($results as $row) {
            $members[] = $row[0];
        }

        $filename = 'member-export-' . date('Y-m-d') . '.csv';
        $response = new Response($memberToCsvService->arrayToCsvString(new ArrayCollection($members)));
        $response->headers->set('Content-type', 'text/csv');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-disposition', $disposition);
        return $response;
    }
}
