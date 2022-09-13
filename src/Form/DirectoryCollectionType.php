<?php

namespace App\Form;

use App\Entity\DirectoryCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DirectoryCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $filterChoices = [
            'Include' => 'include',
            'Exclude' => 'exclude',
            'Ignore' => null,
        ];

        $builder
            ->add('label')
            ->add('description', null, [
                'required' => false,
            ])
            ->add('icon')
            ->add('groupBy', ChoiceType::class, [
                'choices' => [
                    'Class Year' => 'classYear',
                    'Member Status' => 'status',
                    'State' => 'mailingState',
                ],
                'placeholder' => '',
                'required' => false,
            ])
            ->add('showMemberStatus', null, [
                'label' => 'Show Member Status Column?',
            ])
            ->add('memberStatuses', null, [
                'attr' => [
                    'class' => 'selectpicker',
                ],
            ])
            ->add('filterLost', ChoiceType::class, [
                'choices' => $filterChoices,
            ])
            ->add('filterLocalDoNotContact', ChoiceType::class, [
                'choices' => $filterChoices,
            ])
            ->add('filterDeceased', ChoiceType::class, [
                'choices' => $filterChoices,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DirectoryCollection::class,
        ]);
    }
}
