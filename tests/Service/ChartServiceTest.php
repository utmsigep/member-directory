<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\ChartService;
use App\Entity\Member;

class ChartServiceTest extends KernelTestCase
{
    public function testBuildDonationColumnChartData()
    {
        $data = [
            [
                'aggregatedDate' => '2020-01-01',
                'totalDonations' => 12,
                'totalDonors' => 6,
                'totalAmount' => 1200.00,
                'totalProcessingFee' => 13.50,
                'totalNetAmount' => 1186.50,
                'currency' => 'usd'
            ],
            [
                'aggregatedDate' => '2020-02-01',
                'totalDonations' => 10,
                'totalDonors' => 5,
                'totalAmount' => 585.00,
                'totalProcessingFee' => 6.50,
                'totalNetAmount' => 578.50,
                'currency' => 'usd'
            ],
            [
                'aggregatedDate' => '2020-03-01',
                'totalDonations' => 11,
                'totalDonors' => 8,
                'totalAmount' => 600.00,
                'totalProcessingFee' => 6.25,
                'totalNetAmount' => 593.75,
                'currency' => 'usd'
            ],
            [
                'aggregatedDate' => '2020-04-01',
                'totalDonations' => 7,
                'totalDonors' => 4,
                'totalAmount' => 685.13,
                'totalProcessingFee' => 15.13,
                'totalNetAmount' => 670.00,
                'currency' => 'usd'
            ],
        ];
        $output = ChartService::buildDonationColumnChartData($data);
        $this->assertEquals('CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\ColumnChart', get_class($output));
    }
}
