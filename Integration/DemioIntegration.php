<?php

namespace MauticPlugin\DemioBundle\Integration;

use Mautic\IntegrationsBundle\Integration\BasicIntegration;
use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\BasicInterface;

class DemioIntegration extends BasicIntegration implements BasicInterface
{
    use ConfigurationTrait;
    public const NAME                       = 'demio';
    public const DISPLAY_NAME               = 'Demio';
    public const WEBCAST_ATTENDED           = 'attended';
    public const WEBCAST_DID_NOT_ATTENDED   = 'did-not-attended';
    public const WEBCAST_LEFT_EARLY         = 'left-early';
    public const WEBCAST_COMPLETED          = 'completed';
    public const WEBCAST_BANNEd             = 'banned';
    public const SUBSCRIBER_ACTIVITY        = 'SubscriberActivity';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName(): string
    {
        return self::DISPLAY_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'plugins/DemioBundle/Assets/img/demio.png';
    }
}
