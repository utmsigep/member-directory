<?php

namespace App\Repository;

use App\Entity\DirectoryCollection;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findByDirectoryCollection(DirectoryCollection $directoryCollection, $params = []): Paginator
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->join('s.directoryCollections', 'dc')
            ->leftJoin('m.tags', 't')
            ->andWhere('dc = :directoryCollection')
            ->setParameter('directoryCollection', $directoryCollection)
        ;

        if ($directoryCollection->getFilterLost()) {
            $qb->andWhere('m.isLost = :isLost')
                ->setParameter('isLost', $directoryCollection->getFilterLost() == 'include');
        }
        if ($directoryCollection->getFilterLocalDoNotContact()) {
            $qb->andWhere('m.isLocalDoNotContact = :isLocalDoNotContact')
                ->setParameter('isLocalDoNotContact', $directoryCollection->getFilterLocalDoNotContact() == 'include');
        }
        if ($directoryCollection->getFilterDeceased()) {
            $qb->andWhere('m.isDeceased = :isDeceased')
                ->setParameter('isDeceased', $directoryCollection->getFilterDeceased() == 'include');
        }

        $this->processParams($qb, $params);
        return new Paginator($qb->getQuery(), $fetchJoinCollection = true);
    }

    public function findActiveEmailable($params = [])
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->andWhere('s.isInactive = 0')
            ->andWhere('m.primaryEmail != \'\'')
            ->andWhere('m.primaryEmail IS NOT NULL')
        ;
        $this->processParams($qb, $params);
        return new Paginator($qb->getQuery(), $fetchJoinCollection = true);
    }

    public function findLost($params = [])
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->andWhere('m.isLost = 1')
        ;
        $this->processParams($qb, $params);
        return new Paginator($qb->getQuery(), $fetchJoinCollection = true);
    }

    public function findDoNotContact($params = [])
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->andWhere('m.isLocalDoNotContact = 1')
        ;
        $this->processParams($qb, $params);
        return new Paginator($qb->getQuery(), $fetchJoinCollection = true);
    }

    public function findDeceased($params = [])
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->andWhere('m.isDeceased = 1')
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
        ;
        $this->processParams($qb, $params);
        return new Paginator($qb->getQuery(), $fetchJoinCollection = true);
    }

    public function findGeocodedAddresses($params = [])
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->where('m.mailingLatitude IS NOT NULL')
            ->andWhere('m.mailingLatitude != 0')
            ->andWhere('m.mailingLongitude IS NOT NULL')
            ->andWhere('m.mailingLongitude != 0')
            ->andWhere('m.isDeceased = 0')
            ->andWhere('s.isInactive = 0')
        ;
        $this->processParams($qb, $params);
        return new Paginator($qb->getQuery(), $fetchJoinCollection = true);
    }

    public function findMembersWithinRadius(float $latitude, float $longitude, int $radius)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('SELECT
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
                WHERE s.isInactive = false
                    AND m.isDeceased = 0
                HAVING distance < :radius
                ORDER BY distance
            ')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('radius', $radius)
        ;

        return $query->getResult();
    }

    public function findRecentUpdates(array $criteria)
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->where('m.updatedAt > :since')
            ->setParameter('since', $criteria['since'])
            ->orderBy('m.updatedAt', 'DESC')
        ;

        if (isset($criteria['exclude_inactive']) && $criteria['exclude_inactive']) {
            $qb->andWhere('s.isInactive != :isInactive')
                ->setParameter('isInactive', true)
            ;
        }

        return $qb->getQuery()
            ->getResult()
        ;
    }

    public function findByTags(array $tags, array $params = [])
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->addSelect('s')
            ->join('m.status', 's')
            ->leftJoin('m.tags', 't')
            ->leftJoin('m.tags', 't1')
            ->andWhere('t1.id IN (:tags)')
            ->setParameter('tags', $tags)
        ;
        $this->processParams($qb, $params);
        return new Paginator($qb->getQuery(), $fetchJoinCollection = true);
    }

    public function findWithExportFilters(array $filters)
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
            $qb->andWhere('s.isInactive = 0');
        }
        // Return only mailable records
        if (isset($filters['mailable']) && $filters['mailable']) {
            $qb->andWhere('m.mailingAddressLine1 != \'\' OR m.mailingAddressLine2 != \'\'');
        }
        // Return only emailable records
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

    public function search(string $searchTerm, $limit = 1000)
    {
        return $this->createQueryBuilder('m')
            ->addSelect('MATCH (m.firstName, m.preferredName, m.middleName, m.lastName) AGAINST (:searchTerm IN BOOLEAN MODE) AS score')
            ->setParameter('searchTerm', $searchTerm . '*')
            ->having('score > 0')
            ->orderBy('score', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByPrimaryTelephone(string $telephoneNumber): ?Member
    {
        return $this->createQueryBuilder('m')
            ->where('m.primaryTelephoneNumber = :telephoneNumber')
            ->setParameter('telephoneNumber', preg_replace(
                '/.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*/',
                '($1) $2-$3',
                $telephoneNumber)
            )
            ->getQuery()
            ->getSingleResult()
        ;
    }

    private function processParams(QueryBuilder $qb, $params = []): QueryBuilder
    {
        // Pagination
        if (isset($params['limit'], $params['offset'])) {
            $qb->setMaxResults($params['limit']);
            $qb->setFirstResult($params['offset']);
        }

        // Sorting
        if (isset($params['sort_by'], $params['sort_direction'])) {
            $qb->orderBy($params['sort_by'], $params['sort_direction']);
            if (isset($params['group_by']) && $params['group_by']) {
                $qb->orderBy($params['group_by'], 'ASC');
                $qb->addOrderBy($params['sort_by'], $params['sort_direction']);
            }
        } else {
            $qb->orderBy('m.lastName', 'ASC')
                ->addOrderBy('m.firstName', 'ASC');
        }

        return $qb;
    }

}
