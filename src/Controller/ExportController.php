<?php

namespace App\Controller;

use App\Form\MemberExportType;
use App\Repository\MemberRepository;
use App\Service\MemberToCsvService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route(path: '/directory/export')]
class ExportController extends AbstractController
{
    #[Route(path: '/', name: 'export')]
    public function export(Request $request, MemberToCsvService $memberToCsvService, MemberRepository $memberRepository)
    {
        $form = $this->createForm(MemberExportType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $members = $memberRepository->findWithExportFilters($filters);

            $filename = 'member-export-'.date('Y-m-d').'.csv';
            $response = new Response($memberToCsvService->arrayToCsvString(new ArrayCollection($members), $filters['columns']));
            $response->headers->set('Content-type', 'text/csv');
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );
            $response->headers->set('Content-disposition', $disposition);

            return $response;
        }

        return $this->render('member/export.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/by-location', name: 'export_by_location', options: ['expose' => true])]
    public function exportByLocation(Request $request, MemberToCsvService $memberToCsvService, MemberRepository $memberRepository)
    {
        if (!$request->get('latitude') || !$request->get('longitude') || !$request->get('radius')) {
            throw new BadRequestHttpException('Invalid search parameters.');
        }

        $memberStatuses = $request->get('member_statuses', []);
        $results = $memberRepository->findMembersWithinRadius(
            (float) $request->get('latitude'),
            (float) $request->get('longitude'),
            (int) $request->get('radius'),
            ['member_statuses' => $memberStatuses]
        );

        $members = [];
        foreach ($results as $row) {
            $members[] = $row;
        }

        $filename = 'member-export-by-location-'.date('Y-m-d').'.csv';
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
