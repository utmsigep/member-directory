<?php

namespace App\Controller;

use App\Entity\CommunicationLog;
use App\Form\CommunicationLogType;
use App\Repository\CommunicationLogRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/communication")
 */
class CommunicationController extends AbstractController
{
    /**
     * @Route("/", name="communication_index", methods={"GET"})
     */
    public function index(CommunicationLogRepository $communicationLogRepository): Response
    {
        return $this->render('communication/index.html.twig', [
            'communication_logs' => $communicationLogRepository->findBy([], ['loggedAt' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="communication_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $communicationLog = new CommunicationLog();
        $form = $this->createForm(CommunicationLogType::class, $communicationLog, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $communicationLog->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
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

    /**
     * @Route("/{id}", name="communication_show", methods={"GET"})
     */
    public function show(CommunicationLog $communicationLog): Response
    {
        return $this->render('communication/show.html.twig', [
            'communication_log' => $communicationLog,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="communication_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CommunicationLog $communicationLog): Response
    {
        $form = $this->createForm(CommunicationLogType::class, $communicationLog, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', sprintf('%s updated!', $communicationLog));
            return $this->redirectToRoute('communication_index');
        }

        return $this->render('communication/edit.html.twig', [
            'communication_log' => $communicationLog,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="communication_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CommunicationLog $communicationLog): Response
    {
        if ($this->isCsrfTokenValid('delete'.$communicationLog->getId(), $request->request->get('_token'))) {
            $flashMessage = sprintf('%s deleted!', $communicationLog);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($communicationLog);
            $entityManager->flush();
            $this->addFlash('success', $flashMessage);
        }

        return $this->redirectToRoute('communication_index');
    }
}
