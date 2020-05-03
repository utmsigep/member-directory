<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Form\DonationType;
use App\Form\DonationImportType;
use App\Repository\DonationRepository;
use App\Service\DonorboxDonationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/admin/donations")
 */
class DonationController extends AbstractController
{
    /**
     * @Route("/", name="donation_index", methods={"GET"})
     */
    public function index(DonationRepository $donationRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $donations = $entityManager->getRepository(Donation::class)->findAll();
        $totals = $entityManager->getRepository(Donation::class)->getTotalDonations();
        return $this->render('donation/index.html.twig', [
            'donations' => $donations,
            'totals' => $totals
        ]);
    }

    /**
     * @Route("/new", name="donation_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $donation = new Donation();
        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($donation);
            $entityManager->flush();

            return $this->redirectToRoute('donation_index');
        }

        return $this->render('donation/new.html.twig', [
            'donation' => $donation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/donorbox-import", name="donorbox_import")
     */
    public function donorboxImport(Request $request, DonorboxDonationService $donorboxDonationService)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(DonationImportType::class, null);
        $form->handleRequest($request);
        $donations = [];
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $donorboxDonationService->run($form['csv_file']->getData());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(), 500);
            }
            $formData = $form->getData();
            $dryRun = (bool) $formData['dry_run'];
            foreach ($donorboxDonationService->getDonations() as $donation) {
                $entityManager->persist($donation);
            }
            if (!$dryRun) {
                $entityManager->flush();
            }
        }

        return $this->render('donation/import.html.twig', [
            'importForm' => $form->createView(),
            'donations' => $donorboxDonationService->getDonations(),
            'errors' => $donorboxDonationService->getErrors()
        ]);
    }

    /**
     * @Route("/{id}", name="donation_show", methods={"GET"})
     */
    public function show(Donation $donation): Response
    {
        return $this->render('donation/show.html.twig', [
            'donation' => $donation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="donation_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Donation $donation): Response
    {
        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('donation_index');
        }

        return $this->render('donation/edit.html.twig', [
            'donation' => $donation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="donation_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Donation $donation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$donation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($donation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('donation_index');
    }
}
