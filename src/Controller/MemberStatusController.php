<?php

namespace App\Controller;

use App\Entity\DirectoryCollection;
use App\Entity\MemberStatus;
use App\Form\MemberStatusType;
use App\Repository\MemberStatusRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/admin/member-statuses")
 */
class MemberStatusController extends AbstractController
{
    /**
     * @Route("/", name="member_status_index", methods={"GET"})
     */
    public function memberStatus(MemberStatusRepository $memberStatusRepository): Response
    {
        return $this->render('member_status/index.html.twig', [
            'member_statuses' => $memberStatusRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="member_status_new", methods={"GET","POST"})
     */
    public function memberStatusNew(Request $request): Response
    {
        $memberStatus = new MemberStatus();
        $form = $this->createForm(MemberStatusType::class, $memberStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($memberStatus);
            $entityManager->flush();
            if ($form['createDirectoryCollection']->getData()) {
                try {
                    $this->createDirectoryCollectionFromMemberStatus($memberStatus);
                    $this->addFlash('success', sprintf('%s created!', $memberStatus));
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Unable to create Directory Collection automatically.');
                }
            }
            return $this->redirectToRoute('member_status_index');
        }

        return $this->render('member_status/new.html.twig', [
            'member_status' => $memberStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="member_status_show", methods={"GET"})
     */
    public function memberStatusShow(MemberStatus $memberStatus): Response
    {
        return $this->render('member_status/show.html.twig', [
            'member_status' => $memberStatus,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="member_status_edit", methods={"GET","POST"})
     */
    public function memberStatusEdit(Request $request, MemberStatus $memberStatus): Response
    {
        $form = $this->createForm(MemberStatusType::class, $memberStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', sprintf('%s updated!', $memberStatus));
            return $this->redirectToRoute('member_status_index');
        }

        return $this->render('member_status/edit.html.twig', [
            'member_status' => $memberStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="member_status_delete", methods={"DELETE"})
     */
    public function memberStatusDelete(Request $request, MemberStatus $memberStatus): Response
    {
        if ($this->isCsrfTokenValid('delete'.$memberStatus->getId(), $request->request->get('_token'))) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($memberStatus);
                $entityManager->flush();
                $this->addFlash('success', sprintf('%s deleted!', $memberStatus));
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash('error', sprintf('Cannot delete %s because it is used by Members.', $memberStatus));
            }
        }

        return $this->redirectToRoute('member_status_index');
    }

    /**
     * Private Methods
     */
    private function createDirectoryCollectionFromMemberStatus(MemberStatus $memberStatus)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $directoryCollection = new DirectoryCollection();
        $directoryCollection->setLabel($memberStatus->getLabel());
        $directoryCollection->setIcon('fa-user');
        $directoryCollection->setShowMemberStatus(false);
        $directoryCollection->addMemberStatus($memberStatus);
        $entityManager->persist($directoryCollection);
        $entityManager->flush();
    }
}
