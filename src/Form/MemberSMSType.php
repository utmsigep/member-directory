<?php

namespace App\Form;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberSMSType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message_body', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send Message',
            ])
        ;
        if (null == $options['member']) {
            $builder->add('recipients', EntityType::class, [
                'label' => false,
                'class' => Member::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->join('m.status', 's')
                        ->where('s.isInactive = 0')
                        ->andWhere('m.primaryTelephoneNumber != :empty')
                        ->setParameter('empty', '')
                        ->addOrderBy('s.label', 'ASC')
                        ->addOrderBy('m.lastName', 'ASC')
                        ->addOrderBy('m.preferredName', 'ASC')
                    ;
                },
                'group_by' => function ($choice, $key, $value) {
                    return $choice->getStatus()->getLabel();
                },
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => true,
                    'title' => 'Search for Member(s) ...',
                ],
                'multiple' => true,
                'data' => $options['recipients'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'member' => null,
            'acting_user' => null,
            'recipients' => [],
        ]);
    }
}
