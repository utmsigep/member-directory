<?php

namespace App\Repository;

use App\Entity\MemberContactRating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MemberContactRating|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberContactRating|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberContactRating[]    findAll()
 * @method MemberContactRating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberContactRatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberContactRating::class);
    }

    // /**
    //  * @return MemberContactRating[] Returns an array of MemberContactRating objects
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
    public function findOneBySomeField($value): ?MemberContactRating
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
