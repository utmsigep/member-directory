<?php

namespace App\Controller;

use App\Entity\CommunicationLog;
use App\Entity\Event;
use App\Entity\Member;
use App\Form\MemberCommunicationLogType;
use App\Form\MemberEmailType;
use App\Form\MemberSMSType;
use App\Form\MemberType;
use App\Repository\DonationRepository;
use App\Service\ChartService;
use App\Service\CommunicationLogService;
use App\Service\EmailService;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Gedmo\Loggable\Entity\LogEntry;
use Sabre\VObject\Component\VCard;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[IsGranted('ROLE_USER')]
#[Route(path: '/directory/member')]
class MemberController extends AbstractController
{
    #[Route(path: '/new', name: 'member_new')]
    #[IsGranted('ROLE_DIRECTORY_MANAGER')]
    public function memberNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($member);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s created!', $member));

            return $this->redirect($this->generateUrl('member_show', [
                'localIdentifier' => $member->getLocalIdentifier(),
            ]));
        }

        return $this->render('member/new.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{localIdentifier}', name: 'member_show', options: ['expose' => true])]
    public function show(Member $member): Response
    {
        return $this->render('member/show.html.twig', [
            'member' => $member,
        ]);
    }

    #[Route(path: '/{localIdentifier}/edit', name: 'member_edit')]
    #[IsGranted('ROLE_DIRECTORY_MANAGER')]
    public function memberEdit(Member $member, Request $request, EntityManagerInterface $entityManager): Response
    {
        $originalMember = clone $member;
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($member);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s updated!', $member));

            return $this->redirect($this->generateUrl('member_show', [
                'localIdentifier' => $member->getLocalIdentifier(),
            ]));
        }

        return $this->render('member/edit.html.twig', [
            'member' => $originalMember,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{localIdentifier}/delete', name: 'member_delete')]
    #[IsGranted('ROLE_DIRECTORY_MANAGER')]
    public function memberDelete(Member $member, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($member)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->remove($member);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s deleted!', $member));

            return $this->redirect($this->generateUrl('home'));
        }

        return $this->render('member/delete.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{localIdentifier}/change-log', name: 'member_change_log')]
    #[IsGranted('ROLE_DIRECTORY_MANAGER')]
    public function changeLog(Member $member, EntityManagerInterface $entityManager): Response
    {
        $logEntries = $entityManager->getRepository(LogEntry::class)->getLogEntries($member);

        return $this->render('directory/change_log.html.twig', [
            'member' => $member,
            'logEntries' => $logEntries,
        ]);
    }

    #[Route(path: '/{localIdentifier}/communications', name: 'member_communication_log')]
    #[IsGranted('ROLE_COMMUNICATIONS_MANAGER')]
    public function communicationLog(Member $member, Request $request, EntityManagerInterface $entityManager): Response
    {
        $communicationLogs = $entityManager->getRepository(CommunicationLog::class)->getCommunicationLogsByMember($member);
        $communicationLog = new CommunicationLog();
        $form = $this->createForm(MemberCommunicationLogType::class, $communicationLog, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $communicationLog->setMember($member);
            $communicationLog->setUser($this->getUser());
            $entityManager->persist($communicationLog);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s communication logged!', $member));

            return $this->redirectToRoute('member_communication_log', ['localIdentifier' => $member->getLocalIdentifier()]);
        }

        return $this->render('member/communications.html.twig', [
            'member' => $member,
            'communicationLogs' => $communicationLogs,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{localIdentifier}/donations', name: 'member_donations')]
    #[IsGranted('ROLE_DONATION_MANAGER')]
    public function donations(Member $member, DonationRepository $donationRepository, ChartService $chartService, EntityManagerInterface $entityManager): Response
    {
        $donationRepository->setDateRange((new \DateTime())->setTimestamp(0), new \DateTime());
        $donations = $donationRepository->findByMember($member);
        $donationsByMonth = $donationRepository->getTotalDonationsByMonthForMember($member);
        $totals = $donationRepository->getTotalDonationsForMember($member);

        return $this->render('member/donations.html.twig', [
            'member' => $member,
            'donations' => $donations,
            'totals' => $totals,
            'chart_data' => $chartService->buildDonationColumnChartData($donationsByMonth),
        ]);
    }

    #[Route(path: '/{localIdentifier}/events', name: 'member_events')]
    #[IsGranted('ROLE_EVENT_MANAGER')]
    public function events(Member $member, Request $request, EntityManagerInterface $entityManager): Response
    {
        $events = $member->getEvents();
        $form = $this->createFormBuilder()
            ->add('event', EntityType::class, [
                'placeholder' => 'Select Event ...',
                'class' => Event::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.startAt', 'DESC');
                },
                'choice_attr' => function (Event $event) use ($member) {
                    if ($event->getAttendees()->contains($member)) {
                        return ['disabled' => true];
                    }

                    return [];
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->get('event')->getData();
            $event->addAttendee($member);
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s updated!', $member));

            return $this->redirect($this->generateUrl('member_events', [
                'localIdentifier' => $member->getLocalIdentifier(),
            ]));
        }

        return $this->render('member/events.html.twig', [
            'member' => $member,
            'events' => $events,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{localIdentifier}/email-subscription', name: 'member_email_subscription')]
    #[IsGranted('ROLE_EMAIL_MANAGER')]
    public function emailSubscription(Member $member, EmailService $emailService, EntityManagerInterface $entityManager): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');

            return $this->redirectToRoute('member_show', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        if (!$member->getPrimaryEmail()) {
            $this->addFlash('danger', 'No primary email set for Member.');

            return $this->redirectToRoute('member_show', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        $subscriber = $emailService->getMemberSubscription($member);
        $subscriberHistory = $emailService->getMemberSubscriptionHistory($member);

        return $this->render('directory/email_subscription.html.twig', [
            'member' => $member,
            'subscriber' => $subscriber,
            'subscriberHistory' => $subscriberHistory,
        ]);
    }

    #[Route(path: '/{localIdentifier}/add-subscriber', name: 'member_email_subscribe')]
    #[IsGranted('ROLE_EMAIL_MANAGER')]
    public function addSubscriber(Member $member, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');

            return $this->redirectToRoute('member_show', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        if ($emailService->subscribeMember($member, true)) {
            $this->addFlash('success', 'Subscriber record created!');
        } else {
            $this->addFlash('danger', 'Unable to subscribe user. Is the email address valid?');
        }

        return $this->redirectToRoute(
            'member_email_subscription',
            [
                'localIdentifier' => $member->getLocalIdentifier(),
            ]
        );
    }

    #[Route(path: '/{localIdentifier}/update-subscriber', name: 'member_email_update')]
    #[IsGranted('ROLE_EMAIL_MANAGER')]
    public function updateSubscriber(Member $member, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');

            return $this->redirectToRoute('member_show', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        if (!$member->getPrimaryEmail()) {
            $this->addFlash('danger', 'Email not set for Member.');

            return $this->redirectToRoute('member_show', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        if ($emailService->updateMember($member->getPrimaryEmail(), $member)) {
            $this->addFlash('success', 'Subscriber record updated!');
        } else {
            $this->addFlash('danger', 'Unable to update Subscriber record.');
        }

        return $this->redirectToRoute(
            'member_email_subscription',
            [
                'localIdentifier' => $member->getLocalIdentifier(),
            ]
        );
    }

    #[Route(path: '/{localIdentifier}/remove-subscriber', name: 'member_email_remove')]
    #[IsGranted('ROLE_EMAIL_MANAGER')]
    public function removeSubscriber(Member $member, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            $this->addFlash('danger', 'Email service not configured.');

            return $this->redirectToRoute('member_show', ['localIdentifier' => $member->getLocalIdentifier()]);
        }
        if ($emailService->unsubscribeMember($member)) {
            $this->addFlash('success', 'Subscriber record removed!');
        } else {
            $this->addFlash('danger', 'Unable to unsubscribe user.');
        }

        return $this->redirectToRoute(
            'member_email_subscription',
            [
                'localIdentifier' => $member->getLocalIdentifier(),
            ]
        );
    }

    #[Route(path: '/{localIdentifier}/vcard', name: 'member_vcard')]
    public function generateVCard(Member $member): Response
    {
        // Create the VCard
        $vcard = new VCard([
            'FN' => $member->getDisplayName(),
            'N' => [$member->getLastName(), $member->getPreferredName()],
            'TITLE' => $member->getJobTitle(),
            'ORG' => $member->getEmployer(),
        ]);
        $vcard->add('TEL', $member->getPrimaryTelephoneNumber(), ['pref' => 1, 'type' => 'voice']);
        $vcard->add('EMAIL', $member->getPrimaryEmail(), ['pref' => 1]);
        $vcard->add(
            'ADR',
            [
                '',
                $member->getMailingAddressLine1(),
                $member->getMailingAddressLine2(),
                $member->getMailingCity(),
                $member->getMailingState(),
                $member->getMailingPostalCode(),
                $member->getMailingCountry(),
            ],
            [
                'pref' => 1,
                'type' => 'postal',
            ]
        );
        $vcard->validate(\Sabre\VObject\Node::REPAIR);
        $contentDisposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            sprintf('%s.vcf', $member->getDisplayName())
        );

        return new Response($vcard->serialize(), 200, [
            'Content-type' => 'text/x-vcard; charset=utf-8',
            'Content-disposition' => $contentDisposition,
        ]);
    }

    #[Route(path: '/{localIdentifier}/message', name: 'member_message')]
    public function message(Member $member, Request $request, EmailService $emailService, SmsService $smsService, CommunicationLogService $communicationLogService): Response
    {
        $formEmail = $this->createForm(MemberEmailType::class, null, ['member' => $member, 'acting_user' => $this->getUser()]);
        $formEmail->handleRequest($request);
        if ($formEmail->isSubmitted() && $formEmail->isValid()) {
            if (!$member->getPrimaryEmail()) {
                $this->addFlash('danger', 'No email set for member.');

                return $this->redirectToRoute('member_show', ['localIdentifier' => $member->getLocalIdentifier()]);
            }
            $formData = $formEmail->getData();
            $formData['subject'] = $member->formatMemberMessage($formData['subject']);
            $formData['message_body'] = $member->formatMemberMessage($formData['message_body']);
            try {
                $emailService->sendMemberEmail($formData, $member, $this->getUser());
                $this->addFlash('success', 'Email message sent!');
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        $formSMS = $this->createForm(MemberSMSType::class, null, ['member' => $member, 'acting_user' => $this->getUser()]);
        $formSMS->handleRequest($request);
        if ($formSMS->isSubmitted() && $formSMS->isValid()) {
            if (!$member->getPrimaryTelephoneNumber()) {
                $this->addFlash('danger', 'No telephone number set for member.');

                return $this->redirectToRoute('member_show', ['localIdentifier' => $member->getLocalIdentifier()]);
            }
            $formData = $formSMS->getData();
            $message = $member->formatMemberMessage($formData['message_body']);
            try {
                $smsService->sendMemberSms($message, $member, $this->getUser());
                $this->addFlash('success', 'SMS message sent!');
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('directory/message.html.twig', [
            'member' => $member,
            'recentCommunications' => $member->getCommunicationLogs()->slice(0, 10),
            'formEmail' => $formEmail->createView(),
            'fromEmailAddress' => $emailService->getFromEmailAddress(),
            'formSMS' => $formSMS->createView(),
            'fromTelephoneNumber' => $smsService->getFromTelephoneNumber(),
        ]);
    }
}
