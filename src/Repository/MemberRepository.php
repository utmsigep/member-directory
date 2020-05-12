<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function findByStatusCodes($statusCodes = [])
    {
        return $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
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
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->andWhere('s.code IN (:statusCodes)')
            ->andWhere('m.isLost = 1')
            ->setParameter('statusCodes', $statusCodes)
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDoNotContactByStatusCodes($statusCodes = [], $type = null)
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->andWhere('s.code IN (:statusCodes)')
            ->setParameter('statusCodes', $statusCodes)
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC');
        // Filter based on type of DNC
        if ($type == 'local') {
            $qb->andWhere('m.isLocalDoNotContact = 1');
        } elseif ($type == 'external') {
            $qb->andWhere('m.isExternalDoNotContact = 1');
        } else {
            $qb->andWhere('m.isLocalDoNotContact = 1')
                ->orWhere('m.isExternalDoNotContact = 1');
        }
        return $qb->getQuery()
            ->getResult()
        ;
    }

    public function findByStatusCodesGroupByClassYear($statusCodes = [])
    {
        $results = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
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
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->where('m.mailingLatitude IS NOT NULL')
            ->andWhere('m.mailingLatitude != 0')
            ->andWhere('m.mailingLongitude IS NOT NULL')
            ->andWhere('m.mailingLongitude != 0')
            ->andWhere('m.isDeceased = 0')
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
                    m, s, t, (
                      3959 * acos (
                      cos ( radians(:latitude) )
                      * cos( radians( m.mailingLatitude ) )
                      * cos( radians( m.mailingLongitude ) - radians(:longitude) )
                      + sin ( radians(:latitude) )
                      * sin( radians( m.mailingLatitude ) )
                    )
                ) AS distance
                FROM App\Entity\Member m JOIN m.status s LEFT JOIN m.tags t
                WHERE s.code IN (:statusCodes)
                    AND m.isDeceased = 0
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
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->where('m.updatedAt > :since')
            ->andWhere('s.code IN (:statuses)')
            ->setParameter('since', $criteria['since'])
            ->setParameter('statuses', $statuses)
            ->orderBy('m.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByTags($tags = [])
    {
        return $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->leftJoin('m.tags', 't1')
            ->andWhere('t1.id IN (:tags)')
            ->setParameter('tags', $tags)
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findWithExportFilters($filters)
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('s')
            ->join('m.status', 's')
            ->orderBy('m.localIdentifier', 'ASC');
        // Default Filters
        if (isset($filters['default_filters']) && $filters['default_filters']) {
            $qb->andWhere('m.isDeceased = 0');
            $qb->andWhere('m.isLost = 0');
            $qb->andWhere('m.isLocalDoNotContact = 0');
            $qb->andWhere('s.code NOT IN (:resignedExpelled)');
            $qb->setParameter('resignedExpelled', ['RESIGNED', 'EXPELLED']);
        }
        // Return only mailable records
        if (isset($filters['mailable']) && $filters['mailable']) {
            $qb->andWhere('m.mailingAddressLine1 != \'\'');
        }
        // Return only e-mailable records
        if (isset($filters['emailable']) && $filters['emailable']) {
            $qb->andWhere('m.primaryEmail != \'\'');
        }
        // Status Filter
        if (isset($filters['statuses']) && $filters['statuses']->count()) {
            $qb->andWhere('m.status IN (:statuses)');
            $qb->setParameter('statuses', $filters['statuses']);
        }
        // Tag Filter
        if (isset($filters['tags']) && $filters['tags']->count()) {
            foreach ($filters['tags'] as $i => $tag) {
                $qb->innerJoin('m.tags', 't' . $i, Join::WITH, 't' . $i . '.tagName = :tag' . $i);
                $qb->setParameter('tag' . $i, $tag->getTagName());
            }
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function search(string $searchTerm)
    {
        return $this->createQueryBuilder('m')
            ->addSelect('MATCH (m.firstName, m.preferredName, m.middleName, m.lastName) AGAINST (:searchTerm) AS score')
            ->setParameter('searchTerm', $searchTerm)
            ->having('score > 0')
            ->orderBy('score', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
