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
                return $this->abort(400, $e->getMessage());
            }
            $formData = $form->getData();
            $dryRun = (bool) $formData['dryRun'];
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
            }
        }

        return $this->render('import/import-form.html.twig', [
            'importForm' => $form->createView(),
            'members' => $members,
            'newMembers' => $newMembers,
            'memberChangeSets' => $memberChangeSets,
            'errors' => $csvToMemberService->getErrors()
        ]);
    }

    /**
     * @Route("/process", name="import_process", methods={"POST"})
     */
    public function processImportUpdates(Request $request, ValidatorInterface $validator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $members = new ArrayCollection();
        $updates = $request->request->get('members', []);
        $newRecords = $request->request->get('new_records', []);

        // Matched records
        foreach ($updates as $id => $update) {
            $member = $entityManager->getRepository(Member::class)->find((int) $id);
            foreach ($update as $property => $value) {
                if (!in_array($property, self::ALLOWED_PROPERTIES)) {
                    continue;
                }
                if ($property == 'joinDate') {
                    $value = new \DateTime($value);
                }
                $member->{'set' . $property}($value);
            }
            $errors = $validator->validate($member);
            if (count($errors) > 0) {
                $errorMessage = sprintf('Record for "%s" was invalid: ', $member->getDisplayName());
                foreach ($errors as $error) {
                    $errorMessage .= sprintf('[%s] %s;', $error->getPropertyPath(), $error->getMessage());
                }
                $this->addFlash('error', $errorMessage);
                continue;
            }
            $entityManager->persist($member);
            $members->add($member);
        }

        // New Records
        $undergraduateStatus = $entityManager->getRepository(MemberStatus::class)->findOneByCode('UNDERGRADUATE');
        foreach ($newRecords as $id => $update) {
            $member = new Member();
            $member->setStatus($undergraduateStatus);
            foreach ($update as $property => $value) {
                if (!in_array($property, self::ALLOWED_PROPERTIES)) {
                    continue;
                }
                if ($property == 'joinDate') {
                    $value = new \DateTime($value);
                }
                $member->{'set' . $property}($value);
            }
            $errors = $validator->validate($member);
            if (count($errors) > 0) {
                $errorMessage = sprintf('Record for "%s" was invalid: ', $member->getDisplayName());
                foreach ($errors as $error) {
                    $errorMessage .= sprintf('[%s] %s;', $error->getPropertyPath(), $error->getMessage());
                }
                $this->addFlash('error', $errorMessage);
                continue;
            }
            $entityManager->persist($member);
            $members->add($member);
        }
        $entityManager->flush();
        return $this->render('import/summary.html.twig', [
            'records' => $members
        ]);
    }

}
