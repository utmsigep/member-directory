<?php

namespace App\Controller;

use App\Form\MemberImportType;
use App\Service\CsvToMemberService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_DIRECTORY_MANAGER')]
#[Route(path: '/directory/import')]
class ImportController extends AbstractController
{
    #[Route(path: '/', name: 'import')]
    public function import(Request $request, CsvToMemberService $csvToMemberService, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(MemberImportType::class, null);
        $form->handleRequest($request);
        $memberChangeSets = [];
        $members = [];
        $newMembers = [];
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $csvToMemberService->run($form['csv_file']->getData());
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('import');
            }
            $formData = $form->getData();
            $dryRun = (bool) $formData['dry_run'];
            $createNew = (bool) $formData['create_new'];
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
                } elseif ($createNew) {
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

            foreach ($csvToMemberService->getErrors() as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->render('member/import.html.twig', [
            'form' => $form->createView(),
            'members' => $members,
            'newMembers' => $newMembers,
            'memberChangeSets' => $memberChangeSets,
            'allowedProperties' => $csvToMemberService->getAllowedHeaders(),
        ]);
    }
}
