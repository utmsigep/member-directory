<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberUpdateType;
use App\Repository\MemberRepository;
use App\Service\EmailService;
use App\Service\MemberUpdateTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UpdateController extends AbstractController
{
    /**
     * Redirect query string links.
     */
    #[Route(path: '/update-my-info')]
    public function updateFromQueryString(Request $request)
    {
        if ($request->query->get('externalIdentifier') && $request->query->get('updateToken')) {
            return $this->redirectToRoute('self_service_update', [
                'externalIdentifier' => $request->query->get('externalIdentifier'),
                'updateToken' => $request->query->get('updateToken'),
            ]);
        }
        throw $this->createNotFoundException('Member not found.');
    }

    #[Route(path: '/update-my-info/{externalIdentifier}/{updateToken}', name: 'self_service_update')]
    public function update(string $externalIdentifier, string $updateToken, Request $request, EmailService $emailService, MemberRepository $memberRepository, EntityManagerInterface $entityManager, MemberUpdateTokenGenerator $updateTokenGenerator)
    {
        $member = $memberRepository->findOneBy([
            'externalIdentifier' => $externalIdentifier,
        ]);
        // If mismatch of updated token, deceased, or in inactive statuses, ignore
        if (!$member || !$updateTokenGenerator->isValid($member, $updateToken)
            || $member->getIsDeceased()
            || $member->getStatus()->getIsInactive()
        ) {
            $member = new Member();
        }

        $form = $form = $this->createForm(MemberUpdateType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // If form is submitted, member is no longer "lost"
            $member->setIsLost(false);
            $entityManager->persist($member);
            $entityManager->flush();
            $emailService->sendMemberUpdate($member);

            return $this->render('update/confirmation.html.twig');
        }

        return $this->render('update/form.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }
}
