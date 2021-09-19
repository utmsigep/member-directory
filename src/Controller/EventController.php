<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Sabre\VObject\Component\VCalendar;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_EVENT_MANAGER")
 * @Route("/events")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/", name="event_index", methods={"GET"})
     */
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findBy([], ['startAt' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="event_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_show", methods={"GET"})
     */
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @Route("/{id}/ical", name="event_ical", methods={"GET"})
     */
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

    /**
     * @Route("/{id}/edit", name="event_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_delete", methods={"POST"})
     */
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }
}
