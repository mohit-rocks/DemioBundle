<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Integration\Support;

use Mautic\IntegrationsBundle\Integration\Interfaces\SyncInterface;
use Mautic\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use MauticPlugin\DemioBundle\Sync\Mapping\Manual\MappingManualFactory;
use MauticPlugin\DemioBundle\Integration\DemioIntegration;

class SyncSupport extends DemioIntegration implements SyncInterface
{
    /**
     * @var MappingManualFactory
     */
    private $mappingManualFactory;

    /**
     * @var SyncDataExchangeInterface
     */
    private $syncDataExchange;

    public function __construct(MappingManualFactory $mappingManualFactory, SyncDataExchangeInterface $syncDataExchange)
    {
        $this->mappingManualFactory = $mappingManualFactory;
        $this->syncDataExchange     = $syncDataExchange;
    }

    public function getSyncDataExchange(): SyncDataExchangeInterface
    {
        return $this->syncDataExchange;
    }

    public function getMappingManual(): MappingManualDAO
    {
        return $this->mappingManualFactory->getManual();
    }
}
