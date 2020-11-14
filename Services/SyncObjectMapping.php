<?php

namespace MauticPlugin\DemioBundle\Services;

use Doctrine\DBAL\Connection;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MauticPlugin\DemioBundle\Integration\DemioIntegration;

class SyncObjectMapping
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getMappingExistence($internalObjectId, $integrationObjectName, $integrationObjectId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*');
        $qb->from(MAUTIC_TABLE_PREFIX.'sync_object_mapping', 'demio');
        $qb->where('demio.integration = :integration');
        $qb->andWhere('demio.internal_object_name = :internalObjectName');
        $qb->andWhere('demio.internal_object_id = :internalObjectId');
        $qb->andWhere('demio.integration_object_name = :integrationObjectName');
        $qb->andWhere('demio.integration_object_id = :integrationObjectId');

        $qb->setParameters([
            'integration'           => DemioIntegration::NAME,
            'internalObjectName'    => Contact::NAME,
            'internalObjectId'      => $internalObjectId,
            'integrationObjectName' => $integrationObjectName,
            'integrationObjectId'   => $integrationObjectId,
        ]);

        $result = $qb->execute()->fetch();

        return $result ?: null;
    }
}
