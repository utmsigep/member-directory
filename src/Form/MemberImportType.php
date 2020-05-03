<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\File;

class MemberImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('csv_file', FileType::class, [
                'label' => 'CSV to Upload',
                'mapped' => false,
                'row_attr' => [
                    'class' => 'w-50'
                ],
                'attr' => ['placeholder' => 'Choose file ...'],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/csv',
                            'text/plain'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid CSV file.',
                    ])
                ],
            ])
            ->add('dry_run', CheckboxType::class, [
                'label' => 'Dry-run',
                'help' => 'Does not save or create records when checked.',
                'data' => true,
                'required' => false
            ])
        ;
    }
}
