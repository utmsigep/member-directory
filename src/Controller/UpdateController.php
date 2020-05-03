<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mailer\MailerInterface;

use App\Entity\Member;
use App\Form\MemberUpdateType;

class UpdateController extends AbstractController
{
    /**
     * Redirect query string links
     *
     * @Route("/update-my-info")
     */
    public function updateFromQueryString(Request $request)
    {
        if ($request->get('externalIdentifier') && $request->get('updateToken')) {
            return $this->redirectToRoute('self_service_update', [
                'externalIdentifier' => $request->get('externalIdentifier'),
                'updateToken' => $request->get('updateToken')
            ]);
        } else {
            throw $this->createNotFoundException('Member not found.');
        }
    }


    /**
     * @Route("/update-my-info/{externalIdentifier}/{updateToken}", name="self_service_update")
     */
    public function update(Request $request, MailerInterface $mailer)
    {
        $member = $this->getDoctrine()->getRepository(Member::class)->findOneBy([
            'externalIdentifier' => $request->get('externalIdentifier')
        ]);
        // If mismatch of updated token, deceased, or in banned statuses, ignore
        if (!$member || $request->get('updateToken') != $member->getUpdateToken()
            || $member->getIsDeceased()
            || in_array($member->getStatus()->getCode(), ['RESIGNED', 'EXPELLED'])
        ) {
            $member = new Member();
        }

        $form = $form = $this->createForm(MemberUpdateType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            // If form is submitted, member is no longer "lost"
            $member->setIsLost(false);
            // Set headers for grouping in transactional email reporting
            $headers = new Headers();
            $headers->addTextHeader('X-Cmail-GroupName', 'Member Record Update');
            $headers->addTextHeader('X-MC-Tags', 'Member Record Update');
            $message = new TemplatedEmail($headers);
            $message
                ->to($this->getParameter('app.email.to'))
                ->from($this->getParameter('app.email.from'))
                ->subject(sprintf('Member Record Update: %s', $member->getDisplayName()))
                ->htmlTemplate('update/email_update.html.twig')
                ->context(['member' => $member])
                ;
            if ($member->getPrimaryEmail()) {
                $message->replyTo($member->getPrimaryEmail());
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($member);
            $entityManager->flush();
            $mailer->send($message);
            return $this->render('update/confirmation.html.twig');
        }

        return $this->render('update/form.html.twig', [
            'member' => $member,
            'form' => $form->createView()
        ]);
    }
}
