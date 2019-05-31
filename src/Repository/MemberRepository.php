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

    public function findLostByStatusCodes($statusCodes = [])
    {
        return $this->createQueryBuilder('m')
            ->join('m.status', 's')
            ->andWhere('s.code IN (:statusCodes)')
            ->andWhere('m.isLost = 1')
            ->setParameter('statusCodes', $statusCodes)
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByStatusCodesGroupByClassYear($statusCodes = [])
    {
        $results = $this->createQueryBuilder('m')
            ->join('m.status', 's')
            ->andWhere('s.code IN (:statusCodes)')
            ->setParameter('statusCodes', $statusCodes)
            ->orderBy('m.classYear', 'ASC')
            ->addOrderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        $output = [];
        foreach ($results as $row) {
            $output[$row->getClassYear()][] = $row;
        }
        return $output;
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

    public function findMembersWithinRadius($latitude, $longitude, $radius, array $statusCodes = [])
    {
        $entityManager = $this->getEntityManager();
        return $entityManager->createQuery('SELECT
                    m, (
                      3959 * acos (
                      cos ( radians(:latitude) )
                      * cos( radians( m.mailingLatitude ) )
                      * cos( radians( m.mailingLongitude ) - radians(:longitude) )
                      + sin ( radians(:latitude) )
                      * sin( radians( m.mailingLatitude ) )
                    )
                ) AS distance
                FROM App\Entity\Member m JOIN m.status ms
                WHERE ms.code IN (:statusCodes)
                HAVING distance < :radius
                ORDER BY distance
            ')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('radius', $radius)
            ->setParameter('statusCodes', $statusCodes)
            ->getResult()
        ;
    }

    public function findRecentUpdates(array $criteria)
    {
        if (isset($criteria['exclude_inactive']) && $criteria['exclude_inactive']) {
            $statuses = ['ALUMNUS', 'UNDERGRADUATE', 'OTHER'];
        } else {
            $statuses = ['ALUMNUS', 'UNDERGRADUATE', 'OTHER', 'RESIGNED', 'EXPELLED', 'TRANSFERRED'];
        }

        return $this->createQueryBuilder('m')
            ->join('m.status', 's')
            ->where('m.updatedAt > :since')
            ->andWhere('s.code IN (:statuses)')
            ->setParameter('since', $criteria['since'])
            ->setParameter('statuses', $statuses)
            ->orderBy('m.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
