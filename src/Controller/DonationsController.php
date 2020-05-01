<?php

namespace App\Controller;

use Gedmo\Loggable\Entity\LogEntry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\Member;
use App\Entity\Donation;
use App\Service\DonorboxDonationService;
use App\Form\DonationImportType;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/donations")
 */
class DonationsController extends AbstractController
{
    /**
     * @Route("/", name="donations")
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $donations = $entityManager->getRepository(Donation::class)->findAll();
        $totals = $entityManager->getRepository(Donation::class)->getTotalDonations();
        return $this->render('donations/donations.html.twig', [
            'donations' => $donations,
            'totals' => $totals
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

        return $this->render('donations/import-form.html.twig', [
            'importForm' => $form->createView(),
            'donations' => $donorboxDonationService->getDonations(),
            'errors' => $donorboxDonationService->getErrors()
        ]);
    }
}
