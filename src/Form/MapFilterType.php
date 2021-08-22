<?php

namespace App\Form;

use App\Entity\MemberStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MapFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', EntityType::class, [
                'class' => MemberStatus::class,
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function ($choice) {
                    return ['checked' => !$choice->getIsInactive()];
                },
                'label_attr' => array(
                    'class' => 'checkbox-inline'
                )
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //
        ]);
    }
}
