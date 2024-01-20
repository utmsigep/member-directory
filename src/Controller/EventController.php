<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventAttendeeImportType;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\CsvToMemberService;
use App\Service\MemberToCsvService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sabre\VObject\Component\VCalendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EVENT_MANAGER')]
#[Route(path: '/events')]
class EventController extends AbstractController
{
    #[Route(path: '/', name: 'event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findBy([], ['startAt' => 'DESC']),
        ]);
    }

    #[Route(path: '/new', name: 'event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route(path: '/{id}/ical', name: 'event_ical', methods: ['GET'])]
    public function ical(Event $event): Response
    {
        $ical = new VCalendar([
            'VEVENT' => [
                'SUMMARY' => $event->getName(),
                'DESCRIPTION' => $event->getDescription(),
                'DTSTART' => $event->getstartAt(),
                'DTEND' => $event->getStartAt()->add(new \DateInterval('PT3H')),
            ],
        ]);
        $ical->validate(\Sabre\VObject\Node::REPAIR);
        $contentDisposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            sprintf('%s.ics', $event->getName())
        );

        return new Response($ical->serialize(), 200, [
            'Content-type' => 'text/calendar; charset=utf-8',
            'Content-disposition' => $contentDisposition,
        ]);
    }

    #[Route(path: '/{id}/attendee-export', name: 'event_attendee_export', methods: ['GET'])]
    public function attendeeExport(Event $event, MemberToCsvService $memberToCsvService): Response
    {
        $members = $event->getAttendees();
        $filename = $event->getCode().'.csv';
        $response = new Response($memberToCsvService->arrayToCsvString(new ArrayCollection($members->toArray())));
        $response->headers->set('Content-type', 'text/csv');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-disposition', $disposition);

        return $response;
    }

    #[Route(path: '/{id}/attendee-import', name: 'event_attendee_import', methods: ['GET', 'POST'])]
    public function attendeeImport(Event $event, Request $request, EntityManagerInterface $entityManager, CsvToMemberService $csvToMemberService): Response
    {
        $form = $this->createForm(EventAttendeeImportType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $csvToMemberService->run($form['csv_file']->getData());
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('event_attendee_import', ['id' => $event->getId()]);
            }
            $formData = $form->getData();
            foreach ($csvToMemberService->getMembers() as $member) {
                if ($member->getId() > 0) {
                    $event->addAttendee($member);
                }
            }
            $entityManager->persist($event);
            $entityManager->flush();

            foreach ($csvToMemberService->getErrors() as $error) {
                $this->addFlash('error', $error);
            }

            $this->addFlash('success', 'Import complete!');

            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        return $this->render('event/attendee-import.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'allowedProperties' => $csvToMemberService->getAllowedHeaders(),
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }
}
