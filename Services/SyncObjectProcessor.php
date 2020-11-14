<?php

namespace MauticPlugin\DemioBundle\Services;

use Mautic\IntegrationsBundle\Entity\ObjectMapping;
use Mautic\IntegrationsBundle\Entity\ObjectMappingRepository;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PointBundle\Model\PointModel;
use MauticPlugin\DemioBundle\Connection\ApiConsumer;
use MauticPlugin\DemioBundle\Integration\DemioIntegration;
use MauticPlugin\DemioBundle\Sync\Mapping\Manual\MappingManualFactory;

class SyncObjectProcessor
{
    /**
     * @var SyncObjectMapping
     */
    private $syncObjectMapping;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var PointModel
     */
    private $pointModel;

    /**
     * @var ObjectMappingRepository
     */
    private $objectMappingRepository;

    public function __construct(SyncObjectMapping $syncObjectMapping, LeadModel $lead_model, PointModel $pointModel, ObjectMappingRepository $objectMappingRepository)
    {
        $this->syncObjectMapping       = $syncObjectMapping;
        $this->leadModel               = $lead_model;
        $this->pointModel              = $pointModel;
        $this->objectMappingRepository = $objectMappingRepository;
    }

    /**
     * Store relation of lead and sync data in the integrations table.
     *
     * @param int $id
     *   Mautic lead id.
     * @param array $data
     *   Data array from API.
     *
     * @throws \Exception
     */
    public function syncRelations(int $id, int $integrationObjectId, array $data)
    {
        $userData = array_filter($data, function ($value) use ($integrationObjectId) {
            return ($value['uid'] == $integrationObjectId);
        });
        $userData = reset($userData);
        $this->mapContactObject(
            $userData['event_id'],
            $id,
            DemioIntegration::SUBSCRIBER_ACTIVITY,
            $userData['event_id'] . ':' .  $userData['status']
        );
    }

    /**
     * Map and store the integration in the table.
     *
     * @param $integrationId
     * @param $mauticId
     * @param $integrationObjectName
     * @param null $refId
     * @throws \Exception
     */
    private function mapContactObject($integrationId, $mauticId, $integrationObjectName, $refId = null)
    {
        $integrationObject = $this->syncObjectMapping->getMappingExistence(
            $mauticId,
            $integrationObjectName,
            $integrationId
        );

        if (is_null($integrationObject)) {
            $objectMapping = new ObjectMapping();
            $objectMapping->setIntegration(DemioIntegration::NAME)
                ->setIntegrationObjectName($integrationObjectName)
                ->setInternalObjectName(Contact::NAME)
                ->setIntegrationObjectId($integrationId)
                ->setInternalObjectId($mauticId)
                ->setLastSyncDate(new \DateTime());

            if ($refId) {
                $objectMapping->setIntegrationReferenceId($refId);
            }
            $this->saveObjectMapping($objectMapping);
        } else {
            $mappingEntityId = $integrationObject['id'];
            /** @var ObjectMapping $updateObjectMapping */
            $updateObjectMapping  = $this->objectMappingRepository->getEntity($mappingEntityId);

            if (!empty($refId) &&
                $refId !== $updateObjectMapping->getIntegrationReferenceId()
            ) {
                $updateObjectMapping->setIntegrationReferenceId($refId);
                $updateObjectMapping->setLastSyncDate(new \DateTime());
                $this->saveObjectMapping($updateObjectMapping);
            }
        }
    }

    private function saveObjectMapping(ObjectMapping $objectMapping): void
    {
        $this->objectMappingRepository->saveEntity($objectMapping);
        $this->objectMappingRepository->clear();
    }
}
