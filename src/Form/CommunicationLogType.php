<?php

namespace App\Form;

use App\Entity\CommunicationLog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommunicationLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('member')
            ->add('loggedAt', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'html5' => true,
                'model_timezone' => 'UTC',
                'view_timezone' => $options['timezone'],
            ])
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Select One --',
                'choices' => CommunicationLog::COMMUNICATION_TYPES,
            ])
            ->add('summary')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommunicationLog::class,
            'timezone' => 'UTC',
        ]);
    }
}
