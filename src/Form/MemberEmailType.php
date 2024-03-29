<?php

namespace App\Form;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class)
            ->add('message_body', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('reply_to', EmailType::class, [
                'label' => 'Reply To',
                'data' => is_object($options['acting_user']) ? $options['acting_user']->getEmail() : '',
                'required' => false,
            ])
            ->add('send_copy', CheckboxType::class, [
                'label' => 'Send copy of message to yourself?',
                'label_attr' => ['class' => 'small'],
                'data' => false,
                'required' => false,
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
                        ->andWhere('m.primaryEmail != :empty')
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'member' => null,
            'acting_user' => null,
            'recipients' => [],
        ]);
    }
}
