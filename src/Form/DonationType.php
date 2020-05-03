<?php

namespace App\Form;

use App\Entity\Donation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('receiptIdentifier')
            ->add('receivedAt', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('campaign')
            ->add('description')
            ->add('amount')
            ->add('currency', CurrencyType::class, [
                'preferred_choices' => [
                    'USD',
                    'CAD'
                ]
            ])
            ->add('processingFee')
            ->add('netAmount')
            ->add('donorComment')
            ->add('internalNotes')
            ->add('donationType')
            ->add('cardType')
            ->add('lastFour')
            ->add('isAnonymous')
            ->add('isRecurring')
            ->add('member')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Donation::class,
        ]);
    }
}
