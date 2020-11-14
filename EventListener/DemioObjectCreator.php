<?php

namespace MauticPlugin\DemioBundle\EventListener;

use Mautic\IntegrationsBundle\Event\CompletedSyncIterationEvent;
use Mautic\IntegrationsBundle\IntegrationEvents;
use MauticPlugin\DemioBundle\Services\SyncObjectProcessor;
use MauticPlugin\DemioBundle\Sync\Mapping\Manual\MappingManualFactory;
use MauticPlugin\DemioBundle\Integration\DemioIntegration;
use MauticPlugin\DemioBundle\Services\DemioContactStore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DemioObjectCreator implements EventSubscriberInterface
{
    /**
     * @var DemioContactStore
     */
    private $demioContactStore;

    /**
     * @var SyncObjectProcessor
     */
    private $syncObjectProcessor;

    public function __construct(DemioContactStore $demioContactStore, SyncObjectProcessor $syncObjectProcessor)
    {
        $this->demioContactStore  = $demioContactStore;
        $this->syncObjectProcessor     = $syncObjectProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            IntegrationEvents::INTEGRATION_BATCH_SYNC_COMPLETED_INTEGRATION_TO_MAUTIC => 'onSyncComplete',
        ];
    }

    public function onSyncComplete(CompletedSyncIterationEvent $event)
    {
        if (DemioIntegration::NAME == $event->getIntegration()) {
            $orderResult    = $event->getOrderResults();
            $createdObjects = $orderResult->getObjectMappings(MappingManualFactory::DEMIO_OBJECT);

            if (!empty($createdObjects)) {
                // Fetch a list of objects objects from the integration's API.
                $apiData = $this->demioContactStore->getDummySubscribersData();

                foreach ($createdObjects as $object) {
                    $integrationObjectId       = $object->getIntegrationObjectId();
                    $mauticId                  = $object->getInternalObjectId();
                    $this->syncObjectProcessor->syncRelations($mauticId, $integrationObjectId, $apiData);

                }
            }
        }
    }
}
