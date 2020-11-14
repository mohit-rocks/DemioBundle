<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Sync\DataExchange;

use Mautic\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO as ReportObjectDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO as RequestObjectDAO;
use MauticPlugin\DemioBundle\Integration\DemioIntegration;
use MauticPlugin\DemioBundle\Integration\Config;
use MauticPlugin\DemioBundle\Services\DemioContactStore;
use MauticPlugin\DemioBundle\Sync\Mapping\Field\Field;
use MauticPlugin\DemioBundle\Sync\Mapping\Field\FieldRepository;

class ReportBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var FieldRepository
     */
    private $fieldRepository;

    /**
     * @var ValueNormalizer
     */
    private $valueNormalizer;

    /**
     * @var DemioContactStore
     */
    private $demioContactStore;

    /**
     * @var ReportDAO
     */
    private $report;

    public function __construct(Config $config, FieldRepository $fieldRepository, DemioContactStore $demioContactStore)
    {
        $this->config                  = $config;
        $this->fieldRepository         = $fieldRepository;
        $this->demioContactStore       = $demioContactStore;

        // Value normalizer transforms value types expected by each side of the sync
        $this->valueNormalizer         = new ValueNormalizer();
    }

    /**
     * @param RequestObjectDAO[] $requestedObjects
     */
    public function build(int $page, array $requestedObjects, InputOptionsDAO $options): ReportDAO
    {
        $this->report = new ReportDAO(DemioIntegration::NAME);
        //$events = $this->demioContactStore->getAllEvents();
        $events = $this->demioContactStore->getDummyEventsData();
//        $participants = [];
//        foreach ($events as $event) {
//            $participantsData = $this->demioContactStore->getAllParticipants($event->date_id);
//            $participants = array_merge($participants, $participantsData);
//        }
        $participants = $this->demioContactStore->getDummySubscribersData();

        // Set the since date.
        $sinceDateTime =  !is_null($options->getStartDateTime()) ? $options->getStartDateTime() : new \DateTimeImmutable('1 month ago');

        foreach ($requestedObjects as $requestedObject) {
            $objectName = $requestedObject->getObject();

            // Add the modified items to the report
            $this->addModifiedItems($objectName, $participants);
        }

        return $this->report;
    }

    private function addModifiedItems(string $objectName, array $changeList): void
    {
        // Get the the field list to know what the field types are.
        $fields = $this->fieldRepository->getFields($objectName);

        $mappedFields = $this->config->getMappedFields($objectName);

        foreach ($changeList as $item) {
            $objectDAO = new ReportObjectDAO(
                $objectName,
                // Set the ID from the integration.
                $item['uid']
            );

            foreach ($item as $fieldAlias => $fieldValue) {
                if (!isset($fields[$fieldAlias]) || !isset($mappedFields[$fieldAlias])) {
                    // Field is not recognized or it's not mapped so ignore.
                    continue;
                }

                /** @var Field $field */
                $field = $fields[$fieldAlias];

                // The sync is currently from Integration to Mautic so normalize
                // the values for storage in Mautic
                $normalizedValue = $this->valueNormalizer->normalizeForMautic(
                    $fieldValue,
                    $field->getDataType()
                );

                $objectDAO->addField(new FieldDAO($fieldAlias, $normalizedValue));
            }

            // Add the modified/new lead to the report.
            $this->report->addObject($objectDAO);
        }
    }
}
