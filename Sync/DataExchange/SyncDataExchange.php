<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Sync\DataExchange;

use Mautic\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;

class SyncDataExchange implements SyncDataExchangeInterface
{
    /**
     * @var ReportBuilder
     */
    private $reportBuilder;

    /**
     * SyncDataExchange constructor.
     */
    public function __construct(ReportBuilder $reportBuilder)
    {
        $this->reportBuilder    = $reportBuilder;
    }

    public function getSyncReport(RequestDAO $requestDAO): ReportDAO
    {
        return $this->reportBuilder->build(
                $requestDAO->getSyncIteration(),
                $requestDAO->getObjects(),
                $requestDAO->getInputOptionsDAO()
            );
    }

    /**
     * {@inheritdoc}
     */
    public function executeSyncOrder(OrderDAO $syncOrderDAO): void
    {
    }
}
