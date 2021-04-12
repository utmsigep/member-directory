<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('email', EmailType::class, [
                'label' => 'Login Email',
                'constraints' => [
                    new Email([
                        'message' => 'Must be a valid email address!'
                    ])
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Basic User' => 'ROLE_USER',
                    'Donation Manager' => 'ROLE_DONATION_MANAGER',
                    'Email Manager' => 'ROLE_EMAIL_MANAGER',
                    'Site Administrator' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true
            ])
            ->add('plainPassword', RepeatedType::class, [
                'label' => 'Password',
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => $options['require_password'],
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
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
                    'Pacific/Honolulu'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => true
        ]);
    }
}
