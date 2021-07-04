<?php

namespace App\Form;

use App\Entity\Event;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
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
            ->add('description')
            ->add('attendees', null, [
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
