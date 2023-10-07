<?php

namespace App\Form;

use App\Entity\MemberStatus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MapFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', EntityType::class, [
                'class' => MemberStatus::class,
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->andWhere('s.isInactive = false')
                        ->addOrderBy('s.label', 'ASC')
                    ;
                },
                'choice_attr' => function ($choice) {
                    return ['checked' => !$choice->getIsInactive()];
                },
                'label_attr' => [
                    'class' => 'checkbox-inline',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
