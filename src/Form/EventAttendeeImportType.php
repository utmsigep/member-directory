<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class EventAttendeeImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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
        ;
    }
}
