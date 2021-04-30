<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;

class MemberEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class)
            ->add('message_body', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 10
                ]
            ])
            ->add('reply_to', EmailType::class, [
                'label' => 'Reply To',
                'data' => is_object($options['acting_user']) ? $options['acting_user']->getEmail() : '',
                'required' => false
            ])
            ->add('send_copy', CheckboxType::class, [
                'label' => 'Send copy of message to yourself?',
                'label_attr' => ['class' => 'small'],
                'data' => false,
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send Message'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'acting_user' => null
        ));
    }
}
