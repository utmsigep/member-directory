<?php

namespace App\Repository;

use App\Entity\MemberEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MemberEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberEmail[]    findAll()
 * @method MemberEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberEmailRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MemberEmail::class);
    }

    // /**
    //  * @return MemberEmail[] Returns an array of MemberEmail objects
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
    public function findOneBySomeField($value): ?MemberEmail
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
