<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Loggable\Entity\LogEntry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/admin/users')]
class UserController extends AbstractController
{
    #[Route(path: '/', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy([], ['lastLogin' => 'DESC']),
        ]);
    }

    #[Route(path: '/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form['plainPassword']->getData()) {
                $user->setPassword($passwordEncoder->hashPassword(
                    $user,
                    $form['plainPassword']->getData()
                ));
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s created!', $user));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user, ManagerRegistry $doctrine): Response
    {
        $logs = $doctrine->getRepository(LogEntry::class)->findBy(['username' => $user->getUsername()], ['loggedAt' => 'DESC'], 1000);

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'logs' => $logs,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user, ['require_password' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            if ($form['plainPassword']->getData()) {
                $user->setPassword($passwordEncoder->hashPassword(
                    $user,
                    $form['plainPassword']->getData()
                ));
            }
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', sprintf('%s updated!', $user));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/disable-2fa', name: 'user_disable_two_factor', methods: ['POST'])]
    public function disableTwoFactor(Request $request, User $user, LoggerInterface $logger, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('disableTwoFactor'.$user->getId(), $request->request->get('_token'))) {
            $user->setTotpSecret(null);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Two-Factor Security disabled.');
            $logger->info(sprintf(
                '[SECURITY] %s disabled Two-Factor Security on %s',
                (string) $this->getUser(),
                (string) $user
            ));
        }

        return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
    }

    #[Route(path: '/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s deleted!', $user));
        }

        return $this->redirectToRoute('user_index');
    }
}
