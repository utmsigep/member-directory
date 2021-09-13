<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberUpdateType;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UpdateController extends AbstractController
{
    /**
     * Redirect query string links.
     *
     * @Route("/update-my-info")
     */
    public function updateFromQueryString(Request $request)
    {
        if ($request->get('externalIdentifier') && $request->get('updateToken')) {
            return $this->redirectToRoute('self_service_update', [
                'externalIdentifier' => $request->get('externalIdentifier'),
                'updateToken' => $request->get('updateToken'),
            ]);
        }
        throw $this->createNotFoundException('Member not found.');
    }

    /**
     * @Route("/update-my-info/{externalIdentifier}/{updateToken}", name="self_service_update")
     */
    public function update(Request $request, EmailService $emailService)
    {
        $member = $this->getDoctrine()->getRepository(Member::class)->findOneBy([
            'externalIdentifier' => $request->get('externalIdentifier'),
        ]);
        // If mismatch of updated token, deceased, or in banned statuses, ignore
        if (!$member || $request->get('updateToken') != $member->getUpdateToken()
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
            $entityManager = $this->getDoctrine()->getManager();
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
