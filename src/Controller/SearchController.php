<?php

namespace App\Controller;

use App\Repository\MemberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/directory/search")
 */
class SearchController extends AbstractController
{
    /**
     * @Route("/", name="search")
     */
    public function index(MemberRepository $memberRepository, Request $request)
    {
        $results = [];
        if ($request->query->get('searchTerm')) {
            $results = $memberRepository->search($request->query->get('searchTerm'));
        }
        return $this->render('search/index.html.twig', [
            'results' => $results
        ]);
    }
}
