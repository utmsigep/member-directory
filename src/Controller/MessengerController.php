<?php

namespace App\Controller;

use App\Form\MemberEmailType;
use App\Form\MemberSMSType;
use App\Repository\EventRepository;
use App\Repository\MemberRepository;
use App\Repository\TagRepository;
use App\Service\CommunicationLogService;
use App\Service\EmailService;
use App\Service\SmsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_COMMUNICATIONS_MANAGER')]
#[Route(path: '/messenger')]
class MessengerController extends AbstractController
{
    protected $eventRepository;
    protected $memberRepository;
    protected $tagRepository;

    public function __construct(EventRepository $eventRepository, MemberRepository $memberRepository, TagRepository $tagRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->memberRepository = $memberRepository;
        $this->tagRepository = $tagRepository;
    }

    #[Route(path: '/', name: 'messenger_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('messenger_email');
    }

    #[Route(path: '/email', name: 'messenger_email')]
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

    #[Route(path: '/sms', name: 'messenger_sms')]
    public function sms(Request $request, SmsService $smsService, CommunicationLogService $communicationLogService): Response
    {
        if (!$smsService->isConfigured()) {
            $this->addFlash('danger', 'SMS service not configured!');

            return $this->redirectToRoute('messenger_home');
        }
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
        $queryParameters = $request->query->all();

        // List of identifiers
        if (isset($queryParameters['recipients']) && is_array($queryParameters['recipients'])) {
            $queryRecipients = $this->memberRepository->findByLocalIdentifiers($queryParameters['recipients']);
        }

        // Event attendees
        if (isset($queryParameters['event_id']) && is_numeric($queryParameters['event_id'])) {
            $event = $this->eventRepository->find($queryParameters['event_id']);
            if ($event) {
                $queryRecipients = $event->getAttendees()->toArray();
            }
        }

        // Tagged Members
        if (isset($queryParameters['tag_id']) && is_numeric($queryParameters['tag_id'])) {
            $tag = $this->tagRepository->find($request->query->get('tag_id'));
            if ($tag) {
                $queryRecipients = $tag->getMembers()->toArray();
            }
        }

        return $queryRecipients;
    }
}
