<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Gedmo\Loggable\Entity\LogEntry;
use USPS\Address;
use USPS\AddressVerify;

use App\Entity\Member;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/directory")
 */
class DirectoryController extends AbstractController
{
    public function index()
    {
        return $this->redirectToRoute('alumni');
    }

    /**
     * @Route("/member/{localIdentifier}", name="member")
     */
    public function member($localIdentifier)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);
        $logEntries = $entityManager->getRepository(LogEntry::class)->getLogEntries($record);
        return $this->render('directory/member.html.twig', [
            'record' => $record,
            'logEntries' => $logEntries
        ]);
    }

    /**
     * @Route("/member/{localIdentifier}/verify-address", name="verify_address")
     */
    public function validateMemberAddress($localIdentifier)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Member::class)->findOneBy(['localIdentifier' => $localIdentifier]);

        $verify = new AddressVerify($_ENV['USPS_USERNAME']);
        $address = new Address();
        $address->setField('Address1', $record->getMailingAddressLine1());
        $address->setField('Address2', $record->getMailingAddressLine2());
        $address->setCity($record->getMailingCity());
        $address->setState($record->getMailingState());
        $address->setZip5($record->getMailingPostalCode());
        $address->setZip4('');
        $verify->addAddress($address);

        $response = $verify->verify();

        return $this->json($verify->getArrayResponse());
    }

    /**
     * @Route("/alumni", name="alumni")
     * @Route("/", name="home")
     */
    public function alumni()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'ALUMNUS',
            'RENAISSANCE'
        ]);
        return $this->render('directory/alumni.html.twig', [
            'records' => $records,
        ]);
    }

    /**
     * @Route("/undergraduates", name="undergraduates")
     */
    public function undergraduates()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $records = $entityManager->getRepository(Member::class)->findByStatusCodes([
            'UNDERGRADUATE'
        ]);
        return $this->render('directory/undergraduates.html.twig', [
            'records' => $records,
        ]);
    }
}
