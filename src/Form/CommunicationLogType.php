<?php

namespace App\Form;

use App\Entity\CommunicationLog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommunicationLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('member')
            ->add('loggedAt')
            ->add('type')
            ->add('summary')
            ->add('payload')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommunicationLog::class,
        ]);
    }
}
