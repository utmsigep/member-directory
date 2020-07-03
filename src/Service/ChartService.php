<?php

namespace App\Service;

use CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\ColumnChart;

class ChartService {
    public static function buildDonationColumnChartData($data): ColumnChart
    {
        $chartTable = [['Date', 'Donation Amount']];
        foreach ($data as $row) {
            $chartTable[] = [
                new \DateTime($row['aggregatedDate']),
                (float) $row['totalAmount']
            ];
        }
        $chart = new ColumnChart();
        $chart->getData()->setArrayToDataTable($chartTable);
        $chart->getOptions()->getLegend()->setPosition('none');
        $chart->getOptions()->getVAxis()->setFormat('currency');
        $chart->getOptions()->setHeight(400);
        $chart->getOptions()->getHAxis()->setFormat('MMMM y');
        $chart->getOptions()->getHAxis()->setTitle('');

        return $chart;
    }
}
