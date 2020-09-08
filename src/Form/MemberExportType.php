<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use App\Entity\MemberStatus;
use App\Entity\Tag;

class MemberExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('default_filters', CheckboxType::class, [
                'label' => 'Apply default filters',
                'required' => false,
                'data' => true,
                'help' => 'Excludes: Do Not Contact, Lost, Deceased'
            ])
            ->add('mailable', CheckboxType::class, [
                'label' => 'Mailable',
                'required' => false,
                'help' => 'Excludes: Blank Address Line 1'
            ])
            ->add('emailable', CheckboxType::class, [
                'label' => 'E-mailable',
                'required' => false,
                'help' => 'Excludes: Empty E-mail Address'
            ])
            ->add('statuses', EntityType::class, [
                'class' => MemberStatus::class,
                'expanded' => true,
                'multiple' => true,
                'required' => false
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'expanded' => true,
                'multiple' => true,
                'required' => false
            ])
        ;
    }
}
