<?php

namespace App\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Sonata\AdminBundle\Form\Type\ModelListType;

use App\Entity\MemberStatus;

final class MemberStatusAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'ASC',
        '_sort_by' => 'label',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('code')
            ->add('label')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('code')
            ->add('label')
            ->add('members', null, [
                'associated_property' => function ($obj) {
                    return sprintf(
                        '%s %s (%s)',
                        $obj->getPreferredName(),
                        $obj->getLastName(),
                        $obj->getLocalIdentifier()
                    );
                },
                'label' => 'Members'
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('code');
        $datagridMapper->add('label');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('code', null, [
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('label')
            ->add('memberCount', null, [
                'label' => 'Count'
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'show' => []
                ]
            ])
        ;
    }

    public function toString($object): string
    {
        return $object instanceof MemberStatus
            ? $object->getLabel()
            : 'Member Status';
    }
}
