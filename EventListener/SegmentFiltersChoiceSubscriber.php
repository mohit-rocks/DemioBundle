<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\EventListener;

use Mautic\CoreBundle\Helper\CacheStorageHelper;
use Mautic\LeadBundle\Entity\OperatorListTrait;
use Mautic\LeadBundle\Event\LeadListFiltersChoicesEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\DemioBundle\Connection\ApiConsumer;
use MauticPlugin\DemioBundle\Integration\Config;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SegmentFiltersChoiceSubscriber implements EventSubscriberInterface
{
    use OperatorListTrait;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CacheStorageHelper
     */
    private $cache;

    /**
     * @var ApiConsumer
     */
    private $apiConsumer;

    public function __construct(
        Config $config,
        TranslatorInterface $translator,
        CacheStorageHelper $cacheStorageHelper,
        ApiConsumer $apiConsumer
    ) {
        $this->config      = $config;
        $this->translator  = $translator;
        $this->cache       = $cacheStorageHelper;
        $this->apiConsumer = $apiConsumer;
    }

    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return [LeadEvents::LIST_FILTERS_CHOICES_ON_GENERATE => 'onGenerateSegmentFilters'];
    }

    public function onGenerateSegmentFilters(LeadListFiltersChoicesEvent $event): void
    {
        if (!$this->config->isPublished()) {
            return;
        }

        $this->addContactFilter($event);
    }

    /**
     * Add new filters related to webinar's subscription status.
     */
    private function addContactFilter(LeadListFiltersChoicesEvent $event): void
    {
        $eventNames = $this->getEventNames();

        $event->addChoice(
            'lead',
            'demio-attended',
            [
                'label'      => $this->translator->trans('plugin.demio.form.attended'),
                'properties' => [
                    'type' => 'select',
                    'list' => $eventNames,
                ],
                'operators' => [
                    'eq'  => $this->translator->trans('mautic.core.operator.equals'),
                    '!eq' => $this->translator->trans('mautic.core.operator.notequals'),
                ],
            ]
        );

        $event->addChoice(
            'lead',
            'demio-did-not-attended',
            [
                'label'      => $this->translator->trans('plugin.demio.form.did-not-attended'),
                'properties' => [
                    'type' => 'select',
                    'list' => $eventNames,
                ],
                'operators' => [
                    'eq'  => $this->translator->trans('mautic.core.operator.equals'),
                    '!eq' => $this->translator->trans('mautic.core.operator.notequals'),
                ],
            ]
        );

        $event->addChoice(
            'lead',
            'demio-completed',
            [
                'label'      => $this->translator->trans('plugin.demio.form.completed'),
                'properties' => [
                    'type' => 'select',
                    'list' => $eventNames,
                ],
                'operators' => [
                    'eq'  => $this->translator->trans('mautic.core.operator.equals'),
                    '!eq' => $this->translator->trans('mautic.core.operator.notequals'),
                ],
            ]
        );

        $event->addChoice(
            'lead',
            'demio-left-early',
            [
                'label'      => $this->translator->trans('plugin.demio.form.left-early'),
                'properties' => [
                    'type' => 'select',
                    'list' => $eventNames,
                ],
                'operators' => [
                    'eq'  => $this->translator->trans('mautic.core.operator.equals'),
                    '!eq' => $this->translator->trans('mautic.core.operator.notequals'),
                ],
            ]
        );
    }

    /**
     * Get the list of all the Demio webinar/events..
     *
     * @return array
     */
    public function getEventNames()
    {
        $events = [];
        // Get all the event details and prepare options value.
        $eventsData = $this->apiConsumer->fetchDummyEventsData();
        foreach ($eventsData as $event) {
            $events[$event['id']] = $event['name'];
        }

        return $events;
    }
}
