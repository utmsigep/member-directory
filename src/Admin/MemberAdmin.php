<?php

namespace App\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class MemberAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('localIdentifier', TextType::class);
        $formMapper->add('externalIdentifier', TextType::class);
        $formMapper->add('lastName', TextType::class);
        $formMapper->add('firstName', TextType::class);
        $formMapper->add('primaryEmail', TextType::class);
        $formMapper->add('status', EntityType::class);
        $formMapper->add('primaryTelephoneNumber', TextType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('localIdentifier');
        $datagridMapper->add('externalIdentifier');
        $datagridMapper->add('lastName');
        $datagridMapper->add('firstName');
        $datagridMapper->add('status.label', 'doctrine_orm_string', [], ChoiceType::class, [
            'choices' => [
                'Alumnus' => 'Alumnus',
                'Renaissance (Honorary)' => 'Renaissance (Honorary)',
                'Undergraduate' => 'Undergraduate',
                'Expelled' => 'Expelled',
                'Resigned' => 'Resigned',
                'Other / Constituent' => 'Other / Constituent'
            ]
        ]);
        $datagridMapper->add('primaryEmail');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('localIdentifier');
        $listMapper->add('lastName');
        $listMapper->add('firstName');
        $listMapper->add('status.label');
        $listMapper->add('primaryEmail');
    }
}
