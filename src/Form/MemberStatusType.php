<?php

namespace App\Form;

use App\Entity\MemberStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('label')
            ->add('isInactive')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $memberStatus = $event->getData();
            $form = $event->getForm();
            if (!$memberStatus || null === $memberStatus->getId()) {
                $form->add('createDirectoryCollection', CheckboxType::class, [
                    'data' => true,
                    'mapped' => false
                ]);
            }
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MemberStatus::class,
        ]);
    }
}
