<?php

namespace App\Repository;

use App\Entity\MemberStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MemberStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberStatus[]    findAll()
 * @method MemberStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberStatus::class);
    }

    // /**
    //  * @return MemberStatus[] Returns an array of MemberStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MemberStatus
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
