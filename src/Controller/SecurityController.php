<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\TwoFactorVerifyType;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SecurityController extends AbstractController
{
    #[Route(path: '/profile', name: 'app_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Profile updated!');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('security/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/change-password', name: 'app_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $form = $this->createFormBuilder($user)
            ->add('plainPassword', RepeatedType::class, [
                'label' => 'Password',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Choose a password!',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Come on, you can think of a password longer than that!',
                    ]),
                ],
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordEncoder->hashPassword(
                $user,
                $form['plainPassword']->getData()
            ));
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Password updated!');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('security/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/manage-two-factor', name: 'app_manage_two_factor')]
    public function manageTwoFactor(Request $request, TotpAuthenticatorInterface $totpAuthenticatorService, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if ($user->isTotpAuthenticationEnabled()) {
            return $this->render('security/two_factor_manage.html.twig');
        }

        $user->setTotpSecret($totpAuthenticatorService->generateSecret());

        $form = $this->createForm(TwoFactorVerifyType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($totpAuthenticatorService->checkCode($user, $form['two_factor_confirm']->getData())) {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Two-Factor Security setup complete!');

                return $this->redirectToRoute('app_manage_two_factor');
            }
            $this->addFlash('error', 'Your code did not match. Please try again.');
        }

        return $this->render('security/two_factor_setup.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route(path: '/two-factor-qr-code/{totpSecret}', name: 'app_two_factor_qr_code')]
    public function renderTotpQrCode($totpSecret, TotpAuthenticatorInterface $totpAuthenticatorService)
    {
        $user = $this->getUser();
        $user->setTotpSecret($totpSecret);

        $qrCodeContent = $totpAuthenticatorService->getQRContent($user);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($qrCodeContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(400)
            ->margin(0)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }

    #[Route(path: '/disable-two-factor', name: 'app_disable_two_factor', methods: ['POST'])]
    public function disableTwoFactor(Request $request, TotpAuthenticatorInterface $totpAuthenticatorService, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if ($this->isCsrfTokenValid('disableTwoFactor'.$user->getId(), $request->request->get('_token'))) {
            $user->setTotpSecret(null);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Two-Factor Security disabled.');
        }

        return $this->redirectToRoute('app_manage_two_factor');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
