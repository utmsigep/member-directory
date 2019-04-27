<?php

namespace App\Repository;

use App\Entity\MemberAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MemberAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberAddress[]    findAll()
 * @method MemberAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberAddressRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MemberAddress::class);
    }

    // /**
    //  * @return MemberAddress[] Returns an array of MemberAddress objects
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
    public function findOneBySomeField($value): ?MemberAddress
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
