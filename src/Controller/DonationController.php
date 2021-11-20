<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Form\DonationImportType;
use App\Form\DonationType;
use App\Repository\DonationRepository;
use App\Service\ChartService;
use App\Service\DonorboxDonationService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_DONATION_MANAGER")
 * @Route("/donations")
 */
class DonationController extends AbstractController
{
    protected $session;

    protected $startDate;

    protected $endDate;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
        $this->startDate = new \DateTime($this->session->get('donation_start_date', DonationRepository::DEFAULT_START_DATE));
        $this->endDate = new \DateTime($this->session->get('donation_end_date', DonationRepository::DEFAULT_END_DATE));
    }

    /**
     * @Route("/", name="donation_index", methods={"GET"})
     */
    public function index(DonationRepository $donationRepository, ChartService $chartService, Request $request): Response
    {
        $this->handleDateRequest($request);
        $timezone = $this->getUser()->getTimezone() ? $this->getUser()->getTimezone() : 'UTC';
        $donations = $donationRepository->setDateRange($this->startDate, $this->endDate, $timezone)->findAll();
        $donationsByMonth = $donationRepository->setDateRange($this->startDate, $this->endDate, $timezone)->getTotalDonationsByMonth();
        $totals = $donationRepository->setDateRange($this->startDate, $this->endDate, $timezone)->getTotalDonations();

        return $this->render('donation/index.html.twig', [
            'donations' => $donations,
            'totals' => $totals,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'chart_data' => $chartService->buildDonationColumnChartData($donationsByMonth),
        ]);
    }

    /**
     * @Route("/donors", name="donation_donors", methods={"GET"})
     */
    public function donors(DonationRepository $donationRepository, Request $request): Response
    {
        $this->handleDateRequest($request);
        $timezone = $this->getUser()->getTimezone() ? $this->getUser()->getTimezone() : 'UTC';
        $donors = $donationRepository->setDateRange($this->startDate, $this->endDate, $timezone)->getTotalDonationsByMember();
        $totals = $donationRepository->setDateRange($this->startDate, $this->endDate, $timezone)->getTotalDonations();

        return $this->render('donation/donors.html.twig', [
            'donors' => $donors,
            'totals' => $totals,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);
    }

    /**
     * @Route("/campaigns", name="donation_campaigns", methods={"GET"})
     */
    public function campaigns(DonationRepository $donationRepository, Request $request): Response
    {
        $this->handleDateRequest($request);
        $timezone = $this->getUser()->getTimezone() ? $this->getUser()->getTimezone() : 'UTC';
        $campaigns = $donationRepository->setDateRange($this->startDate, $this->endDate, $timezone)->getTotalDonationsByCampaign();
        $totals = $donationRepository->setDateRange($this->startDate, $this->endDate, $timezone)->getTotalDonations();

        return $this->render('donation/campaigns.html.twig', [
            'campaigns' => $campaigns,
            'totals' => $totals,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);
    }

    /**
     * @Route("/new", name="donation_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $donation = new Donation();
        $form = $this->createForm(DonationType::class, $donation, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($donation);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s created!', $donation));

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
    public function donorboxImport(Request $request, DonorboxDonationService $donorboxDonationService, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(DonationImportType::class, null);
        $form->handleRequest($request);
        $donations = [];
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $donorboxDonationService->run($form['csv_file']->getData());
                foreach ($donorboxDonationService->getErrors() as $error) {
                    $this->addFlash('warning', $error);
                }
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
            'form' => $form->createView(),
            'donations' => $donorboxDonationService->getDonations(),
            'allowedProperties' => $donorboxDonationService->getAllowedHeaders(),
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
     * @Route("/{id}/edit", name="donation_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Donation $donation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DonationType::class, $donation, ['timezone' => $this->getUser()->getTimezone()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s updated!', $donation));

            return $this->redirectToRoute('donation_index');
        }

        return $this->render('donation/edit.html.twig', [
            'donation' => $donation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="donation_delete", methods={"POST"})
     */
    public function delete(Request $request, Donation $donation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$donation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($donation);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s deleted!', $donation));
        }

        return $this->redirectToRoute('donation_index');
    }

    private function handleDateRequest(Request $request)
    {
        if ($request->query->get('reset')) {
            $this->startDate = new \DateTime(DonationRepository::DEFAULT_START_DATE);
            $this->session->set('donation_start_date', $this->startDate->format('Y-m-d'));
            $this->endDate = new \DateTime(DonationRepository::DEFAULT_END_DATE);
            $this->session->set('donation_end_date', $this->endDate->format('Y-m-d'));
            $this->addFlash('info', 'Reset to default dates.');

            return;
        }
        if ($request->query->get('start_date')) {
            try {
                $this->startDate = new \DateTime($request->query->get('start_date'));
                $this->session->set('donation_start_date', $this->startDate->format('Y-m-d'));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Invalid start date provided.');

                return;
            }
        }
        if ($request->query->get('end_date')) {
            try {
                $this->endDate = new \DateTime($request->query->get('end_date'));
                $this->session->set('donation_end_date', $this->endDate->format('Y-m-d'));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Invalid end date provided.');

                return;
            }
        }

        if ($this->startDate > $this->endDate) {
            $this->addFlash('error', 'Start date cannot be after end date.');
        }
    }
}
