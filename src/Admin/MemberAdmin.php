<?php

namespace App\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

final class MemberAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('localIdentifier', TextType::class);
        $formMapper->add('externalIdentifier', TextType::class);
        $formMapper->add('lastName', TextType::class);
        $formMapper->add('firstName', TextType::class);
        $formMapper->add('primaryEmail', TextType::class);
        $formMapper->add('primaryTelephoneNumber', TextType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('localIdentifier');
        $datagridMapper->add('externalIdentifier');
        $datagridMapper->add('lastName');
        $datagridMapper->add('firstName');
        $datagridMapper->add('primaryEmail');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('localIdentifier');
        $listMapper->add('lastName');
        $listMapper->add('firstName');
        $listMapper->add('primaryEmail');
    }
}
