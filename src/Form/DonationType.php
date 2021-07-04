<?php

namespace App\Form;

use App\Entity\Donation;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('receiptIdentifier')
            ->add('receivedAt', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'html5' => true,
                'model_timezone' => 'UTC',
                'view_timezone' => $options['timezone']
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
            ->add('member', null, [
                'placeholder' => '',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->join('m.status', 's')
                        ->addOrderBy('s.label', 'ASC')
                        ->addOrderBy('m.lastName', 'ASC')
                        ->addOrderBy('m.preferredName', 'ASC')
                    ;
                },
                'group_by' => function($choice, $key, $value) {
                    return $choice->getStatus()->getLabel();
                }
            ])
            ->add('donorFirstName')
            ->add('donorLastName')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Donation::class,
            'timezone' => 'UTC'
        ]);
    }
}
