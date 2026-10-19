<?php

namespace App\Controller;

use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route(path: '/directory/search')]
class SearchController extends AbstractController
{
    #[Route(path: '/', name: 'search', options: ['expose' => true])]
    public function index(MemberRepository $memberRepository, Request $request)
    {
        $results = [];
        if ($request->query->get('q')) {
            $results = $memberRepository->search($request->query->get('q'));
        }

        return $this->render('search/index.html.twig', [
            'results' => $results,
        ]);
    }

    #[Route(path: '/autocomplete', name: 'search_autocomplete', options: ['expose' => true])]
    public function autoComplete(MemberRepository $memberRepository, Request $request)
    {
        $output = [];
        if ($request->query->get('q')) {
            $results = $memberRepository->search($request->query->get('q'), 10);
            foreach ($results as $member) {
                $output[] = [
                    'localIdentifier' => $member[0]->getLocalIdentifier(),
                    'displayName' => $member[0]->getDisplayName(),
                ];
            }
        }

        return $this->json($output);
    }
}
