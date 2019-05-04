<?php

namespace App\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sonata\AdminBundle\Form\Type\ModelType;

use App\Entity\Member;
use App\Entity\MemberStatus;

final class MemberAdmin extends AbstractAdmin
{

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'ASC',
        '_sort_by' => 'lastName',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Membership Record')
                ->with('Basics', ['class' => 'col-md-4'])
                    ->add('localIdentifier')
                    ->add('externalIdentifier')
                    ->add('firstName')
                    ->add('preferredName')
                    ->add('middleName')
                    ->add('lastName')
                    ->add('primaryEmail')
                    ->add('primaryTelephoneNumber')
                    ->add('facebookIdentifier')
                    ->end()
                ->with('Mailing Address', ['class' => 'col-md-4'])
                    ->add('mailingAddressLine1')
                    ->add('mailingAddressLine2')
                    ->add('mailingCity')
                    ->add('mailingState')
                    ->add('mailingPostalCode')
                    ->add('mailingCountry')
                    ->end()
                ->with('GPS Coordinates', ['class' => 'col-md-4'])
                    ->add('mailingLatitude')
                    ->add('mailingLongitude')
                    ->end()
                ->with('Details', ['class' => 'col-md-4'])
                    ->add('joinDate', DateType::class)
                    ->add('classYear')
                    ->add('status', ModelType::class, [
                        'btn_add' => false
                    ])
                    ->end()
                ->with('Contact Flags', ['class' => 'col-md-4'])
                    ->add('isLost', null, [
                        'label' => 'Lost Alumnus'
                    ])
                    ->add('isLocalDoNotContact', null, [
                        'label' => 'Do Not Contact (Local)'
                    ])
                    ->add('isExternalDoNotContact', null, [
                        'label' => 'Do Not Contact (National)'
                    ])
                    ->end()
                ->end()
            ->tab('Additional Information')
                ->with('Work Information', ['class' => 'col-md-6'])
                    ->add('employer')
                    ->add('jobTitle')
                    ->add('occupation')
                    ->end()
                ->with('Notes', ['class' => 'col-md-6'])
                    ->add('directoryNotes')
                    ->end()
                ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->tab('Membership Record')
            ->with('Basics', ['class' => 'col-md-4'])
                ->add('localIdentifier')
                ->add('externalIdentifier')
                ->add('firstName')
                ->add('preferredName')
                ->add('middleName')
                ->add('lastName')
                ->add('primaryEmail')
                ->add('primaryTelephoneNumber')
                ->add('facebookIdentifier')
                ->end()
            ->with('Mailing Address', ['class' => 'col-md-4'])
                ->add('mailingAddressLine1')
                ->add('mailingAddressLine2')
                ->add('mailingCity')
                ->add('mailingState')
                ->add('mailingPostalCode')
                ->add('mailingCountry')
                ->end()
            ->with('GPS Coordinates', ['class' => 'col-md-4'])
                ->add('mailingLatitude')
                ->add('mailingLongitude')
                ->end()
            ->with('Details', ['class' => 'col-md-4'])
                ->add('joinDate')
                ->add('classYear')
                ->add('status')
                ->end()
            ->with('Contact Flags', ['class' => 'col-md-4'])
                ->add('isLost', 'choice', [
                    'label' => 'Lost Alumnus',
                    'choices' => [
                        true => 'Yes',
                        false => 'No'
                    ]
                ])
                ->add('isLocalDoNotContact', 'choice', [
                    'label' => 'Do Not Contact (Local)',
                    'choices' => [
                        true => 'Yes',
                        false => 'No'
                    ]
                ])
                ->add('isExternalDoNotContact', 'choice', [
                    'label' => 'Do Not Contact (National)',
                    'choices' => [
                        true => 'Yes',
                        false => 'No'
                    ]
                ])
                ->end()
            ->end()
        ->tab('Additional Information')
            ->with('Work Information', ['class' => 'col-md-6'])
                ->add('employer')
                ->add('jobTitle')
                ->add('occupation')
                ->end()
            ->with('Notes', ['class' => 'col-md-6'])
                ->add('directoryNotes')
                ->end()
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('localIdentifier')
            ->add('externalIdentifier')
            ->add('preferredName')
            ->add('firstName')
            ->add('lastName')
            ->add('status', null, [
                'show_filter' => true
            ], EntityType::class, [
                'class' => MemberStatus::class,
                'choice_label' => 'label'
            ])
            ->add('classYear')
            ->add('primaryEmail')
            ->add('primaryTelephoneNumber')
            ->add('mailingState')
            ->add('mailingCity')
            ->add('isLost', null, [
                'label' => 'Lost?'
            ])
            ->add('isLocalDoNotContact', null, [
                'label' => 'Do Not Contact (Local)?'
            ])
            ->add('isExternalDoNotContact', null, [
                'label' => 'Do Not Contact (National)?'
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('localIdentifier', null, [
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('preferredName')
            ->add('lastName')
            ->add('status', null, [
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('classYear')
            ->add('primaryEmail', 'email')
            ->add('primaryTelephoneNumber')
            ->add('mailingCity')
            ->add('mailingState')
            ->add('isLost', 'choice', [
                'label' => 'Lost?',
                'choices' => [
                    true => 'Yes',
                    false => ''
                ]
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'show' => []
                ]
            ])
        ;
    }

    public function getObjectMetadata($object)
    {
        $provider = $this->pool->getProvider($object->getProviderName());
        $url = $provider->generatePublicUrl($object, $provider->getFormatName($object, 'admin'));
        return new Metadata($object->getName(), $object->getDescription(), $url);
    }

    public function toString($object): string
    {
        return $object instanceof Member
            ? sprintf(
                '%s %s (%s)',
                $object->getPreferredName(),
                $object->getLastName(),
                $object->getLocalIdentifier()
            )
            : 'Member';
    }
}
