<?php

namespace App\Repository;

use App\Entity\MemberLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MemberLink|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberLink|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberLink[]    findAll()
 * @method MemberLink[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberLinkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MemberLink::class);
    }

    // /**
    //  * @return MemberLink[] Returns an array of MemberLink objects
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
    public function findOneBySomeField($value): ?MemberLink
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
