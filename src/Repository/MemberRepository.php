<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function findByStatusCodes($statusCodes = [])
    {
        return $this->createQueryBuilder('m')
            ->join('m.status', 's')
            ->andWhere('s.code IN (:statusCodes)')
            ->setParameter('statusCodes', $statusCodes)
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findGeocodedAddresses($statusCodes = [])
    {
        return $this->createQueryBuilder('m')
            ->join('m.status', 's')
            ->where('m.mailingLatitude IS NOT NULL')
            ->andWhere('m.mailingLatitude != 0')
            ->andWhere('m.mailingLongitude IS NOT NULL')
            ->andWhere('m.mailingLongitude != 0')
            ->andWhere('s.code IN (:statusCodes)')
            ->setParameter('statusCodes', $statusCodes)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Member
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
