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
use App\Entity\MemberStatus;
use App\Service\CsvToMemberService;
use App\Form\MemberImportType;
use App\Form\MemberType;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/directory/import")
 */
class ImportController extends AbstractController
{
    const ALLOWED_PROPERTIES = [
        'localIdentifier',
        'externalIdentifier',
        'firstName',
        'preferredName',
        'middleName',
        'lastName',
        'mailingAddressLine1',
        'mailingAddressLine2',
        'mailingCity',
        'mailingState',
        'mailingPostalCode',
        'mailingCountry',
        'employer',
        'jobTitle',
        'occupation',
        'primaryTelephoneNumber',
        'primaryEmail',
        'classYear',
        'joinDate'
    ];

    /**
     * @Route("/", name="import")
     */
    public function import(Request $request, CsvToMemberService $csvToMemberService)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(MemberImportType::class, null);
        $form->handleRequest($request);
        $memberChangeSets = [];
        $members = [];
        $newMembers = [];
        if ($form->isSubmitted()) {
            try {
                $csvToMemberService->run($form['csv_file']->getData());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(), 500);
            }
            $formData = $form->getData();
            $dryRun = (bool) $formData['dry_run'];
            foreach ($csvToMemberService->getMembers() as $member) {
                if ($member->getId() > 0) {
                    $members[] = $member;
                    $unitOfWork = $entityManager->getUnitOfWork();
                    $unitOfWork->computeChangeSets();
                    $changes = $unitOfWork->getEntityChangeSet($member);
                    foreach ($changes as $field => &$change) {
                        if (in_array($field, ['status'])) {
                            $change[0] = (string) $change[0];
                            $change[1] = (string) $change[1];
                        }
                    }
                    $memberChangeSets[$member->getId()] = $changes;
                } else {
                    $newMembers[] = $member;
                }
                $entityManager->persist($member);
            }
            if (!$dryRun) {
                $entityManager->flush();
                $this->addFlash('success', 'Import complete!');
            } else {
                $this->addFlash('info', 'Import dry-run complete!');
            }
        }

        return $this->render('import/import_form.html.twig', [
            'importForm' => $form->createView(),
            'members' => $members,
            'newMembers' => $newMembers,
            'memberChangeSets' => $memberChangeSets,
            'errors' => $csvToMemberService->getErrors()
        ]);
    }
}
