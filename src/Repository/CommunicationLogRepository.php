<?php

namespace App\Repository;

use App\Entity\CommunicationLog;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommunicationLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommunicationLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommunicationLog[]    findAll()
 * @method CommunicationLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommunicationLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommunicationLog::class);
    }

    public function getCommunicationLogsByMember(Member $member)
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.member = :member')
            ->setParameter('member', $member)
            ->orderBy('c.loggedAt', 'DESC')
        ;

        return $qb->getQuery()
            ->getResult();
    }
}
