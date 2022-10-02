<?php

namespace App\Controller;

use App\Entity\CommunicationLog;
use App\Form\CommunicationLogType;
use App\Repository\CommunicationLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_COMMUNICATIONS_MANAGER')]
#[Route(path: '/communications')]
class CommunicationController extends AbstractController
{
    #[Route(path: '/', name: 'communication_index', methods: ['GET'])]
    public function index(CommunicationLogRepository $communicationLogRepository): Response
    {
        return $this->render('communication/index.html.twig', [
            'communication_logs' => $communicationLogRepository->findBy([], ['loggedAt' => 'DESC']),
        ]);
    }

    #[Route(path: '/new', name: 'communication_new', methods: ['GET', 'POST'])]
    public function new(EntityManagerInterface $entityManager, Request $request): Response
    {
        $communicationLog = new CommunicationLog();
        $form = $this->createForm(CommunicationLogType::class, $communicationLog, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $communicationLog->setUser($this->getUser());
            $entityManager->persist($communicationLog);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s created!', $communicationLog));

            return $this->redirectToRoute('communication_index');
        }

        return $this->render('communication/new.html.twig', [
            'communication_log' => $communicationLog,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'communication_show', methods: ['GET'])]
    public function show(CommunicationLog $communicationLog): Response
    {
        return $this->render('communication/show.html.twig', [
            'communication_log' => $communicationLog,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'communication_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CommunicationLog $communicationLog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommunicationLogType::class, $communicationLog, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s updated!', $communicationLog));

            return $this->redirectToRoute('communication_index');
        }

        return $this->render('communication/edit.html.twig', [
            'communication_log' => $communicationLog,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'communication_delete', methods: ['POST'])]
    public function delete(Request $request, CommunicationLog $communicationLog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$communicationLog->getId(), $request->request->get('_token'))) {
            $flashMessage = sprintf('%s deleted!', $communicationLog);
            $entityManager->remove($communicationLog);
            $entityManager->flush();
            $this->addFlash('success', $flashMessage);
        }

        return $this->redirectToRoute('communication_index');
    }
}
