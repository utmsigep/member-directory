<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('email', EmailType::class, [
                'label' => 'Login Email',
                'constraints' => [
                    new Email([
                        'message' => 'Must be a valid email address!',
                    ]),
                ],
            ])
            ->add('timezone', TimezoneType::class, [
                'preferred_choices' => [
                    'America/New_York',
                    'America/Chicago',
                    'America/Denver',
                    'America/Phoenix',
                    'America/Los_Angeles',
                    'America/Anchorage',
                    'America/Adak',
                    'Pacific/Honolulu',
                ],
                'attr' => [
                    'class' => 'selectpicker',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
