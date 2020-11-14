<?php

declare(strict_types=1);

return [
    'name'        => 'Demio',
    'description' => 'Enables integration with Demio API.',
    'version'     => '1.0.0',
    'author'      => 'Mohit Aghrea.',
    'routes'      => [
        'main'   => [],
        'public' => [],
        'api'    => [],
    ],
    'services' => [
        'integrations' => [
            // Basic definitions with name, display name and icon
            'mautic.integration.demio' => [
                'class' => \MauticPlugin\DemioBundle\Integration\DemioIntegration::class,
                'tags'  => [
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            // Provides the form types to use for the configuration UI
            'demio.integration.configuration' => [
                'class'     => \MauticPlugin\DemioBundle\Integration\Support\ConfigSupport::class,
                'arguments' => [
                    'demio.sync.repository.fields',
                ],
                'tags'      => [
                    'mautic.config_integration',
                ],
            ],
            // Defines the mapping manual and sync data exchange service for the sync engine
            'demio.integration.sync'          => [
                'class'     => \MauticPlugin\DemioBundle\Integration\Support\SyncSupport::class,
                'arguments' => [
                    'demio.sync.mapping_manual.factory',
                    'demio.sync.data_exchange',
                ],
                'tags'      => [
                    'mautic.sync_integration',
                ],
            ],
        ],
        'sync' => [
            'demio.sync.repository.fields'      => [
                'class'     => \MauticPlugin\DemioBundle\Sync\Mapping\Field\FieldRepository::class,
            ],
            'demio.sync.mapping_manual.factory' => [
                'class'     => \MauticPlugin\DemioBundle\Sync\Mapping\Manual\MappingManualFactory::class,
                'arguments' => [
                    'demio.sync.repository.fields',
                    'demio.integration.config',
                ],
            ],
            // Proxies the actions of the sync between Mautic and this integration to the appropriate services
            'demio.sync.data_exchange' => [
                'class'     => \MauticPlugin\DemioBundle\Sync\DataExchange\SyncDataExchange::class,
                'arguments' => [
                    'demio.sync.data_exchange.report_builder',
                ],
            ],
            // Builds a report of updated and new objects from the integration to sync with Mautic
            'demio.sync.data_exchange.report_builder' => [
                'class'     => \MauticPlugin\DemioBundle\Sync\DataExchange\ReportBuilder::class,
                'arguments' => [
                    'demio.integration.config',
                    'demio.sync.repository.fields',
                    'demio.service.sync.contact_store',
                ],
            ],
        ],
        'other' => [
            // Provides access to configured API keys, settings, field mapping, etc
            'demio.integration.config' => [
                'class'     => \MauticPlugin\DemioBundle\Integration\Config::class,
                'arguments' => [
                    'mautic.integrations.helper',
                ],
            ],
            // The http client used to communicate with the integration which in this case uses OAuth2 client_credentials grant
            'demio.connection.client' => [
                'class'     => \MauticPlugin\DemioBundle\Connection\Client::class,
                'arguments' => [
                    'mautic.helper.cache_storage',
                    'router',
                    'monolog.logger.mautic',
                    'demio.integration.config',
                ],
            ],
            'demio.connection.client.consumer' => [
                'class'     => \MauticPlugin\DemioBundle\Connection\ApiConsumer::class,
                'arguments' => [
                    'mautic.helper.cache_storage',
                    'monolog.logger.mautic',
                    'demio.connection.client',
                    'demio.integration.config',
                ],
            ],
            'demio.service.sync.contact_store' => [
                'class'     => \MauticPlugin\DemioBundle\Services\DemioContactStore::class,
                'arguments' => [
                    'demio.connection.client.consumer',
                ],
            ],
            'demio.service.sync.object_processor' => [
                'class'     => \MauticPlugin\DemioBundle\Services\SyncObjectProcessor::class,
                'arguments' => [
                    'mautic.integration.demio.sync.object.mapping',
                    'mautic.lead.model.lead',
                    'mautic.point.model.point',
                    'mautic.integrations.repository.object_mapping',
                ],
            ],
            'demio.integration.query.builder.demio.contact' => [
                'class'     => \MauticPlugin\DemioBundle\Segment\Query\Filter\DemioContactFilterQueryBuilder::class,
                'arguments' => [
                    'mautic.lead.model.random_parameter_name',
                    'event_dispatcher',
                ],
            ],
            'mautic.integration.demio.sync.object.mapping' => [
                'class'     => \MauticPlugin\DemioBundle\Services\SyncObjectMapping::class,
                'arguments' => [
                    'database_connection',
                ],
            ],
        ],
        'forms' => [
            'demio.integration.form.config_auth' => [
                'class'     => \MauticPlugin\DemioBundle\Form\Type\ConfigAuthType::class,
                'arguments' => [
                    'demio.connection.client',
                ],
            ],
            'demio.integration.form.webcast_activities' => [
                'class'     => \MauticPlugin\DemioBundle\Form\Type\DemioEventActivities::class,
                'arguments' => [
                    'demio.service.sync.contact_store',
                ],
            ],
        ],
        'events' => [
            'demio.integration.leadbundle.object_creator' => [
                'class'     => \MauticPlugin\DemioBundle\EventListener\DemioObjectCreator::class,
                'arguments' => [
                    'demio.service.sync.contact_store',
                    'demio.service.sync.object_processor',
                ],
            ],
            'demio.integration.leadbundle.subscriber' => [
                'class'     => \MauticPlugin\DemioBundle\EventListener\SegmentFiltersChoiceSubscriber::class,
                'arguments' => [
                    'demio.integration.config',
                    'translator',
                    'mautic.helper.cache_storage',
                    'demio.connection.client.consumer',
                ],
            ],
            'demio.integration.leadbundle.dictionary_subscriber' => [
                'class'     => \MauticPlugin\DemioBundle\EventListener\DemioSegmentFiltersDictionarySubscriber::class,
                'arguments' => [
                    'demio.integration.config',
                ],
            ],
        ],
    ],
];
