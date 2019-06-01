<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

use App\Entity\Member;

class UpdateController extends AbstractController
{
    /**
     * @Route("/update-my-info", name="self_service_update")
     */
    public function update(Request $request, MailerInterface $mailer)
    {
        // Load data if identifier present, and token matches
        if ($request->get('externalIdentifier') && $request->get('updateToken')) {
            $member = $this->getDoctrine()->getRepository(Member::class)->findOneBy([
                'externalIdentifier' => $request->get('externalIdentifier')
            ]);
            if ($request->get('updateToken') != $member->getUpdateToken()) {
                $member = new Member();
            }
        } else {
            $member = new Member();
        }

        $form = $this->createFormBuilder($member)
            ->add('classYear', null, [
                'label' => 'Class Year'
            ])
            ->add('mailingAddressLine1', null, [
                'label' => 'Mailing Address'
            ])
            ->add('mailingAddressLine2', null, [
                'label' => false
            ])
            ->add('mailingCity', null, [
                'label' => 'City'
            ])
            ->add('mailingState', null, [
                'label' => 'State'
            ])
            ->add('mailingPostalCode', null, [
                'label' => 'Postal Code'
            ])
            ->add('primaryEmail', null, [
                'label' => 'Primary Email Address',
                'attr' => [
                    'placeholder' => 'user@example.com'
                ]
            ])
            ->add('primaryTelephoneNumber', null, [
                'label' => 'Primary Telephone Number',
                'attr' => [
                    'placeholder' => '(xxx) xxx-xxxx'
                ]
            ])
            ->add('employer')
            ->add('jobTitle', null, [
                'label' => 'Job Title'
            ])
            ->add('occupation', null, [
                'label' => 'Occupation/Industry'
            ])
            ->add('Submit', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            $message = new TemplatedEmail();
            $message
                ->to($this->getParameter('app.email.to'))
                ->from($this->getParameter('app.email.from'))
                ->subject(sprintf('Member Record Update: %s', $member->getDisplayName()))
                ->htmlTemplate('update/email-update.html.twig')
                ->context(['member' => $member])
                ;
            $mailer->send($message);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($member);
            $entityManager->flush();
            return $this->render('update/confirmation.html.twig');
        }

        return $this->render('update/form.html.twig', [
            'member' => $member,
            'form' => $form->createView()
        ]);
    }
}
