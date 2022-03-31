<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberEmailType;
use App\Form\MemberSMSType;
use App\Service\CommunicationLogService;
use App\Service\EmailService;
use App\Service\SmsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_COMMUNICATIONS_MANAGER")
 * @Route("/messenger")
 */
class MessengerController extends AbstractController
{
    /**
     * @Route("/", name="messenger_home")
     */
    public function home(): Response
    {
        return $this->redirectToRoute('messenger_email');
    }

    /**
     * @Route("/email", name="messenger_email")
     */
    public function email(Request $request, EmailService $emailService, CommunicationLogService $communicationLogService): Response
    {
        $queryRecipients = $this->buildRecipientsFromRequest($request);

        $formEmail = $this->createForm(MemberEmailType::class, null, [
            'acting_user' => $this->getUser(),
            'recipients' => $queryRecipients,
        ]);
        $formEmail->handleRequest($request);
        if ($formEmail->isSubmitted() && $formEmail->isValid()) {
            $formData = $formEmail->getData();
            $originalFormData = $formData;
            foreach ($formData['recipients'] as $member) {
                if (!$member->getPrimaryEmail()) {
                    $this->addFlash('danger', sprintf('No email set for %s', $member));
                    continue;
                }
                if ($member->getIsLocalDoNotContact() || $member->getStatus()->getIsInactive()) {
                    $this->addFlash('danger', sprintf('Blocked from contacting %s', $member));
                    continue;
                }

                $formData['subject'] = $member->formatMemberMessage($originalFormData['subject']);
                $formData['message_body'] = $member->formatMemberMessage($originalFormData['message_body']);
                try {
                    $emailService->sendMemberEmail($formData, $member, $this->getUser());
                    $this->addFlash('success', sprintf('Email sent to %s!', $member));
                } catch (\Exception $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
        }

        return $this->render('messenger/email.html.twig', [
            'formEmail' => $formEmail->createView(),
            'fromEmailAddress' => $emailService->getFromEmailAddress(),
        ]);
    }

    /**
     * @Route("/sms", name="messenger_sms")
     */
    public function sms(Request $request, SmsService $smsService, CommunicationLogService $communicationLogService): Response
    {
        $queryRecipients = $this->buildRecipientsFromRequest($request);

        $formSMS = $this->createForm(MemberSMSType::class, null, [
            'acting_user' => $this->getUser(),
            'recipients' => $queryRecipients,
        ]);
        $formSMS->handleRequest($request);
        if ($formSMS->isSubmitted() && $formSMS->isValid()) {
            $formData = $formSMS->getData();
            foreach ($formData['recipients'] as $member) {
                if (!$member->getPrimaryTelephoneNumber()) {
                    $this->addFlash('danger', sprintf('No phone number set for %s', $member));
                    continue;
                }
                if ($member->getIsLocalDoNotContact() || $member->getStatus()->getIsInactive()) {
                    $this->addFlash('danger', sprintf('Blocked from contacting %s', $member));
                    continue;
                }
                $message = $member->formatMemberMessage($formData['message_body']);
                try {
                    $smsService->sendMemberSms($message, $member, $this->getUser());
                    $this->addFlash('success', sprintf('SMS message sent to %s!', $member));
                } catch (\Exception $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
        }

        return $this->render('messenger/sms.html.twig', [
            'formSMS' => $formSMS->createView(),
            'fromTelephoneNumber' => $smsService->getFromTelephoneNumber(),
        ]);
    }

    private function buildRecipientsFromRequest(Request $request): array
    {
        $queryRecipients = [];

        // List of identifiers
        if (is_array($request->query->get('recipients'))) {
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            $queryRecipients = $memberRepository->findByLocalIdentifiers($request->query->get('recipients'));
        }

        return $queryRecipients;
    }

}
