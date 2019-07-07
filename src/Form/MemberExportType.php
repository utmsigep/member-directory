<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\MemberStatus;
use App\Entity\Tag;

class MemberExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('submit', SubmitType::class, [
                'label' => 'Create Export'
            ])
        ;
    }
}
