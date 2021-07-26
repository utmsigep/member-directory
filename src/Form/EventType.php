<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('code', null, ['required' => false])
            ->add('startAt', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'html5' => true,
                'model_timezone' => 'UTC',
                'view_timezone' => $options['timezone']
            ])
            ->add('location')
            ->add('description', null, [
                'empty_data' => '',
                'required' => false
            ])
            ->add('attendees', CollectionType::class, [
                'label' => false,
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Member::class,
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
                    },
                    'attr' => [
                        'class' => 'selectpicker',
                        'data-live-search' => true,
                        'title' => 'Search for Member ...'
                    ]
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'timezone' => 'UTC'
        ]);
    }
}
