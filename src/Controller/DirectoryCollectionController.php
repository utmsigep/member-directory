<?php

namespace App\Controller;

use App\Entity\DirectoryCollection;
use App\Form\DirectoryCollectionType;
use App\Repository\DirectoryCollectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/admin/directory-collections')]
class DirectoryCollectionController extends AbstractController
{
    #[Route(path: '/', name: 'directory_collection_index', methods: ['GET'])]
    public function index(DirectoryCollectionRepository $directoryCollectionRepository): Response
    {
        return $this->render('directory_collection/index.html.twig', [
            'directory_collections' => $directoryCollectionRepository->findBy([], ['position' => 'ASC']),
        ]);
    }

    #[Route(path: '/new', name: 'directory_collection_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $directoryCollection = new DirectoryCollection();
        $form = $this->createForm(DirectoryCollectionType::class, $directoryCollection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($directoryCollection);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s created!', $directoryCollection));

            return $this->redirectToRoute('directory_collection_index');
        }

        return $this->render('directory_collection/new.html.twig', [
            'directory_collection' => $directoryCollection,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'directory_collection_show', methods: ['GET'])]
    public function show(DirectoryCollection $directoryCollection): Response
    {
        return $this->render('directory_collection/show.html.twig', [
            'directory_collection' => $directoryCollection,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'directory_collection_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DirectoryCollection $directoryCollection, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DirectoryCollectionType::class, $directoryCollection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s updated!', $directoryCollection));

            return $this->redirectToRoute('directory_collection_index');
        }

        return $this->render('directory_collection/edit.html.twig', [
            'directory_collection' => $directoryCollection,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'directory_collection_delete', methods: ['POST'])]
    public function delete(Request $request, DirectoryCollection $directoryCollection, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$directoryCollection->getId(), $request->request->get('_token'))) {
            $entityManager->remove($directoryCollection);
            $entityManager->flush();
        }
        $this->addFlash('success', sprintf('%s deleted!', $directoryCollection));

        return $this->redirectToRoute('directory_collection_index');
    }

    #[Route(path: '/{id}/reorder', name: 'directory_collection_reorder', methods: ['POST'], options: ['expose' => true])]
    public function reorder(Request $request, DirectoryCollection $directoryCollection, EntityManagerInterface $entityManager, $id)
    {
        $position = (int) $request->request->get('position');
        $directoryCollection->setPosition($position);
        $entityManager->persist($directoryCollection);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'data' => [
                'id' => $id,
                'position' => $position,
            ],
        ]);
    }
}
