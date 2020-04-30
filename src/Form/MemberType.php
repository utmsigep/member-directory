<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('localIdentifier')
            ->add('externalIdentifier')
            ->add('firstName')
            ->add('preferredName')
            ->add('middleName')
            ->add('lastName')
            ->add('joinDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('classYear')
            ->add('isDeceased')
            ->add('primaryEmail')
            ->add('primaryTelephoneNumber')
            ->add('mailingAddressLine1')
            ->add('mailingAddressLine2')
            ->add('mailingCity')
            ->add('mailingState')
            ->add('mailingPostalCode')
            ->add('mailingCountry')
            ->add('mailingLatitude', null, [
                'scale' => 8
            ])
            ->add('mailingLongitude', null, [
                'scale' => 8
            ])
            ->add('employer')
            ->add('jobTitle')
            ->add('occupation')
            ->add('facebookIdentifier')
            ->add('linkedinUrl')
            ->add('isLost')
            ->add('isLocalDoNotContact')
            ->add('isExternalDoNotContact')
            ->add('directoryNotes')
            ->add('status')
            ->add('tags')
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
