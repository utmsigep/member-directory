<?php

namespace App\Form;

use App\Entity\Member;
use App\Repository\TagRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    private $tagRepository;

    public function __construct(TagRepository $tagRespository)
    {
        $this->tagRepository = $tagRespository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('localIdentifier')
            ->add('externalIdentifier')
            ->add('firstName')
            ->add('preferredName')
            ->add('middleName')
            ->add('lastName')
            ->add('birthDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
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
            ->add('mailingLatitude', NumberType::class, [
                'scale' => 8,
                'attr' => [
                    'step' => '0.0000001',
                ],
                'html5' => true,
            ])
            ->add('mailingLongitude', NumberType::class, [
                'scale' => 8,
                'attr' => [
                    'step' => '0.0000001',
                ],
                'html5' => true,
            ])
            ->add('employer')
            ->add('jobTitle')
            ->add('occupation')
            ->add('facebookUrl')
            ->add('linkedinUrl')
            ->add('photoUrl')
            ->add('isLost')
            ->add('isLocalDoNotContact')
            ->add('directoryNotes')
            ->add('status')
        ;
        if (count($this->tagRepository->findAll())) {
            $builder->add('tags', null, [
              'by_reference' => false,
          ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
