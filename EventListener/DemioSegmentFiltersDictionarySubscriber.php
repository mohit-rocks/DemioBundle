<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\EventListener;

use Mautic\LeadBundle\Event\SegmentDictionaryGenerationEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\DemioBundle\Integration\Config;
use MauticPlugin\DemioBundle\Segment\Query\Filter\DemioContactFilterQueryBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DemioSegmentFiltersDictionarySubscriber implements EventSubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::SEGMENT_DICTIONARY_ON_GENERATE => 'onGenerateSegmentDictionary',
        ];
    }

    public function onGenerateSegmentDictionary(SegmentDictionaryGenerationEvent $event): void
    {
        if (!$this->config->isPublished()) {
            return;
        }
        $event->addTranslation(
            'demio-attended',
            [
                'type'          => DemioContactFilterQueryBuilder::getServiceId(),
                'field'         => 'lead',
                'table'         => 'sync_object_mapping',
            ]
        );

        $event->addTranslation(
            'demio-did-not-attended',
            [
                'type'  => DemioContactFilterQueryBuilder::getServiceId(),
                'field' => 'lead',
                'table' => 'sync_object_mapping',
            ]
        );

        $event->addTranslation(
            'demio-completed',
            [
                'type'  => DemioContactFilterQueryBuilder::getServiceId(),
                'field' => 'lead',
                'table' => 'sync_object_mapping',
            ]
        );

        $event->addTranslation(
            'demio-left-early',
            [
                'type'  => DemioContactFilterQueryBuilder::getServiceId(),
                'field' => 'lead',
                'table' => 'sync_object_mapping',
            ]
        );

        $event->addTranslation(
            'demio-banned',
            [
                'type'  => DemioContactFilterQueryBuilder::getServiceId(),
                'field' => 'lead',
                'table' => 'sync_object_mapping',
            ]
        );
    }
}
