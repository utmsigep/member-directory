<?php

namespace App\Repository;

use App\Entity\Donation;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Donation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Donation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Donation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DonationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donation::class);
    }

    public function findAll()
    {
        return $this->createQueryBuilder('d')
            ->addSelect('m')
            ->addSelect('t')
            ->join('d.member', 'm')
            ->leftJoin('m.tags', 't')
            ->orderBy('d.receivedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByMember(Member $member)
    {
        return $this->createQueryBuilder('d')
            ->addSelect('m')
            ->addSelect('t')
            ->join('d.member', 'm')
            ->leftJoin('m.tags', 't')
            ->where('d.member = :member')
            ->setParameter('member', $member)
            ->orderBy('d.receivedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalDonations()
    {
        return $this->createQueryBuilder('d')
            ->select('sum(d.amount) AS totalAmount, sum(d.processingFee) AS totalProcessingFee, sum(d.netAmount) AS totalNetAmount, d.currency')
            ->groupBy('d.currency')
            ->getQuery()
            ->getResult();
    }

    public function getTotalDonationsForMember(Member $member)
    {
        return $this->createQueryBuilder('d')
            ->select('sum(d.amount) AS totalAmount, sum(d.processingFee) AS totalProcessingFee, sum(d.netAmount) AS totalNetAmount, d.currency')
            ->join('d.member', 'm')
            ->where('d.member = :member')
            ->setParameter('member', $member)
            ->groupBy('d.currency')
            ->getQuery()
            ->getResult();
    }
}
