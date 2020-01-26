<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;

final class DonationAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'receivedAt',
    ];

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('receiptIdentifier')
            ->add('member')
            ->add('receivedAt')
            ->add('campaign')
            ->add('description')
            ->add('amount')
            ->add('currency')
            ->add('processingFee')
            ->add('netAmount')
            ->add('donorComment')
            ->add('internalNotes')
            ->add('donationType')
            ->add('cardType')
            ->add('lastFour')
            ->add('isAnonymous')
            ->add('isRecurring')
            ->add('createdAt')
            ->add('updatedAt')
            ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('receiptIdentifier', null, [
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('member')
            ->add('receivedAt')
            ->add('campaign')
            ->add('description')
            ->add('amount')
            ->add('currency')
            ->add('processingFee')
            ->add('netAmount')
            ->add('donationType')
            ->add('isAnonymous')
            ->add('isRecurring')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
                ->with('Basics', ['class' => 'col-md-4'])
                    ->add('receiptIdentifier')
                    ->add('member')
                    ->add('campaign', null, ['required' => false, 'empty_data' => ''])
                    ->add('description', null, ['required' => false, 'empty_data' => ''])
                    ->end()
                ->with('Amount', ['class' => 'col-md-4'])
                    ->add('currency', CurrencyType::class)
                    ->add('amount')
                    ->add('processingFee')
                    ->add('netAmount')
                    ->end()
                ->with('Additional Details', ['class' => 'col-md-4'])
                    ->add('donorComment', null, ['required' => false, 'empty_data' => ''])
                    ->add('internalNotes', null, ['required' => false, 'empty_data' => ''])
                    ->add('donationType')
                    ->add('cardType', null, ['required' => false, 'empty_data' => ''])
                    ->add('lastFour', null, ['required' => false, 'empty_data' => ''])
                    ->add('isAnonymous')
                    ->add('isRecurring')
                    ->end()
            ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
        ->with('Basics', ['class' => 'col-md-4'])
            ->add('receiptIdentifier')
            ->add('member')
            ->add('receivedAt')
            ->add('campaign', null, ['required' => false])
            ->add('description', null, ['required' => false])
            ->end()
        ->with('Amount', ['class' => 'col-md-4'])
            ->add('currency')
            ->add('amount')
            ->add('processingFee')
            ->add('netAmount')
            ->end()
        ->with('Additional Details', ['class' => 'col-md-4'])
            ->add('donorComment', null, ['required' => false])
            ->add('internalNotes', null, ['required' => false])
            ->add('donationType')
            ->add('cardType', null, ['required' => false])
            ->add('lastFour', null, ['required' => false])
            ->add('isAnonymous')
            ->add('isRecurring')
            ->end()
    ;
    }
}
