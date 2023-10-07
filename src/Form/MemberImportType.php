<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class MemberImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('csv_file', FileType::class, [
                'label' => 'CSV to Upload',
                'mapped' => false,
                'attr' => ['placeholder' => 'Choose file ...'],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/csv',
                            'text/plain',
                            'application/csv',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid CSV file.',
                    ]),
                ],
            ])
            ->add('create_new', CheckboxType::class, [
                'label' => 'Create New Records',
                'help' => 'Unmatched records are created when checked',
                'data' => true,
                'required' => false,
            ])
            ->add('dry_run', CheckboxType::class, [
                'label' => 'Dry-run',
                'help' => 'Does not save or create records when checked.',
                'data' => false,
                'required' => false,
            ])
        ;
    }
}
