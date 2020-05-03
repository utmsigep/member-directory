<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                'label' => 'Primary E-mail Address',
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
            ->add('linkedinUrl', UrlType::class, [
                'label' => 'LinkedIn URL',
                'attr' => [
                    'placeholder' => 'e.g. https://www.linkedin.com/in/williamhgates/'
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
