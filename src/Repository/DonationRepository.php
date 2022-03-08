<?php

namespace App\Repository;

use App\Entity\Donation;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Donation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Donation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Donation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DonationRepository extends ServiceEntityRepository
{
    public const DEFAULT_START_DATE = '-1 years midnight';
    public const DEFAULT_END_DATE = 'tomorrow -1 min';

    protected $startDate;

    protected $endDate;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donation::class);
        $this->startDate = (new \DateTime(self::DEFAULT_START_DATE))->setTime(0, 0, 0);
        $this->endDate = (new \DateTime(self::DEFAULT_END_DATE))->setTime(23, 59, 59);
    }

    public function setDateRange(\DateTime $startDate, \DateTime $endDate): DonationRepository
    {
        $startDate->setTime(0, 0, 0);
        $this->startDate = $startDate;
        $endDate->setTime(23, 59, 59);
        $this->endDate = $endDate;

        return $this;
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('d')
            ->addSelect('m')
            ->addSelect('t')
            ->leftJoin('d.member', 'm')
            ->leftJoin('m.tags', 't')
            ->andWhere('d.receivedAt >= :startDate')
            ->andWhere('d.receivedAt <= :endDate')
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
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
            ->andWhere('d.receivedAt >= :startDate')
            ->andWhere('d.receivedAt <= :endDate')
            ->setParameter('member', $member)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->orderBy('d.receivedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalDonations()
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d) AS totalDonations, COUNT(DISTINCT d.member) AS totalDonors, SUM(d.amount) AS totalAmount, SUM(d.processingFee) AS totalProcessingFee, SUM(d.netAmount) AS totalNetAmount, MAX(d.receivedAt) AS latestDonation, d.currency')
            ->groupBy('d.currency')
            ->andWhere('d.receivedAt >= :startDate')
            ->andWhere('d.receivedAt <= :endDate')
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->getQuery()
            ->getResult();
    }

    public function getTotalDonationsByMember()
    {
        return $this->createQueryBuilder('d')
            ->select('IDENTITY(d.member) as memberId, m.preferredName, m.lastName, m.localIdentifier, COUNT(d) AS totalDonations, COUNT(DISTINCT d.member) AS totalDonors, SUM(d.amount) AS totalAmount, SUM(d.processingFee) AS totalProcessingFee, SUM(d.netAmount) AS totalNetAmount, MAX(d.receivedAt) AS latestDonation, d.currency, d.isAnonymous')
            ->join('d.member', 'm')
            ->andWhere('d.receivedAt >= :startDate')
            ->andWhere('d.receivedAt <= :endDate')
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->groupBy('d.currency', 'd.member', 'd.isAnonymous')
            ->orderBy('m.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalDonationsByCampaign()
    {
        return $this->createQueryBuilder('d')
            ->select('d.campaign, COUNT(d) AS totalDonations, COUNT(DISTINCT d.member) AS totalDonors, SUM(d.amount) AS totalAmount, SUM(d.processingFee) AS totalProcessingFee, SUM(d.netAmount) AS totalNetAmount, MAX(d.receivedAt) AS latestDonation, d.currency')
            ->groupBy('d.currency', 'd.campaign')
            ->andWhere('d.receivedAt >= :startDate')
            ->andWhere('d.receivedAt <= :endDate')
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->orderBy('latestDonation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalDonationsByMonth()
    {
        return $this->createQueryBuilder('d')
            ->select('DATE_FORMAT(d.receivedAt, \'%Y-%m-01\') AS aggregatedDate, COUNT(d) AS totalDonations, COUNT(DISTINCT d.member) AS totalDonors, SUM(d.amount) AS totalAmount, SUM(d.processingFee) AS totalProcessingFee, SUM(d.netAmount) AS totalNetAmount, d.currency')
            ->andWhere('d.receivedAt >= :startDate')
            ->andWhere('d.receivedAt <= :endDate')
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->groupBy('d.currency', 'aggregatedDate')
            ->orderBy('aggregatedDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalDonationsForMember(Member $member)
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d) AS totalDonations, SUM(d.amount) AS totalAmount, SUM(d.processingFee) AS totalProcessingFee, SUM(d.netAmount) AS totalNetAmount, MAX(d.receivedAt) AS latestDonation, d.currency')
            ->join('d.member', 'm')
            ->where('d.member = :member')
            ->andWhere('d.receivedAt >= :startDate')
            ->andWhere('d.receivedAt <= :endDate')
            ->setParameter('member', $member)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->groupBy('d.currency')
            ->getQuery()
            ->getResult();
    }

    public function getTotalDonationsByMonthForMember(Member $member)
    {
        return $this->createQueryBuilder('d')
            ->select('DATE_FORMAT(d.receivedAt, \'%Y-%m-01\') AS aggregatedDate, COUNT(d) AS totalDonations, COUNT(DISTINCT d.member) AS totalDonors, SUM(d.amount) AS totalAmount, SUM(d.processingFee) AS totalProcessingFee, SUM(d.netAmount) AS totalNetAmount, d.currency')
            ->groupBy('d.currency', 'aggregatedDate')
            ->orderBy('aggregatedDate', 'ASC')
            ->join('d.member', 'm')
            ->where('d.member = :member')
            ->andWhere('d.receivedAt >= :startDate')
            ->andWhere('d.receivedAt <= :endDate')
            ->setParameter('member', $member)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->getQuery()
            ->getResult();
    }
}
