<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Sync\Mapping\Manual;

use Mautic\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MauticPlugin\DemioBundle\Integration\DemioIntegration;
use MauticPlugin\DemioBundle\Integration\Config;
use MauticPlugin\DemioBundle\Sync\Mapping\Field\Field;
use MauticPlugin\DemioBundle\Sync\Mapping\Field\FieldRepository;

class MappingManualFactory
{
    public const DEMIO_OBJECT = 'participant';
    /**
     * @var MappingManualDAO
     */
    private $manual;

    /**
     * @var FieldRepository
     */
    private $fieldRepository;

    /**
     * @var Config
     */
    private $config;

    public function __construct(FieldRepository $fieldRepository, Config $config)
    {
        $this->fieldRepository = $fieldRepository;
        $this->config          = $config;
    }

    public function getManual(): MappingManualDAO
    {
        if ($this->manual) {
            return $this->manual;
        }

        // Instructions to the sync engine on how to map fields and the direction of data should flow
        $this->manual = new MappingManualDAO(DemioIntegration::NAME);

        $this->configureObjectMapping(self::DEMIO_OBJECT);

        return $this->manual;
    }

    private function configureObjectMapping(string $objectName): void
    {
        // Get a list of available fields from the integration
        $fields = $this->fieldRepository->getFields($objectName);

        // Get a list of fields mapped by the user
        $mappedFields = $this->config->getMappedFields($objectName);

        // Generate an object mapping DAO for the given object. The object must be mapped to a supported Mautic object (i.e. contact or company)
        $objectMappingDAO = new ObjectMappingDAO(Contact::NAME, $objectName);

        foreach ($mappedFields as $fieldAlias => $mauticFieldAlias) {
            if (!isset($fields[$fieldAlias])) {
                // The mapped field is no longer available
                continue;
            }

            /** @var Field $field */
            $field = $fields[$fieldAlias];

            // Configure how fields should be handled by the sync engine as determined by the user's configuration.
            $objectMappingDAO->addFieldMapping(
                $mauticFieldAlias,
                $fieldAlias,
                ObjectMappingDAO::SYNC_TO_MAUTIC,
                $field->isRequired()
            );

            $this->manual->addObjectMapping($objectMappingDAO);
        }
    }
}
