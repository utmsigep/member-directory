<?php

namespace App\Repository;

use App\Entity\DirectoryCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DirectoryCollection|null find($id, $lockMode = null, $lockVersion = null)
 * @method DirectoryCollection|null findOneBy(array $criteria, array $orderBy = null)
 * @method DirectoryCollection[]    findAll()
 * @method DirectoryCollection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DirectoryCollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DirectoryCollection::class);
    }

    public function getDefaultDirectoryCollection()
    {
        return $this->createQueryBuilder('d')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
