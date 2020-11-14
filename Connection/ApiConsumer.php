<?php

namespace MauticPlugin\DemioBundle\Connection;

use Mautic\CoreBundle\Helper\CacheStorageHelper;
use MauticPlugin\DemioBundle\Integration\DemioIntegration;
use MauticPlugin\DemioBundle\Integration\Config;
use Monolog\Logger;
use phpDocumentor\Reflection\Types\Integer;

class ApiConsumer
{
    /**
     * @var \Mautic\CoreBundle\Helper\CacheStorageHelper
     */
    private $cacheProvider;
    /**
     * @var \Monolog\Logger
     */
    private $logger;
    /**
     * @var \MauticPlugin\DemioBundle\Connection\Client
     */
    private $client;
    /**
     * @var \MauticPlugin\DemioBundle\Integration\Config
     */
    private $config;

    public function __construct(
        CacheStorageHelper $cacheProvider,
        Logger $logger,
        Client $client,
        Config $config
    ) {
        $this->cacheProvider    = $cacheProvider;
        $this->logger           = $logger;
        $this->client           = $client;
        $this->config           = $config;
    }

    /**
     * Fetch all the events.
     */
    public function fetchEvents()
    {
        return $this->client->get('/events');
    }

    /**
     * Get event information for the event.
     *
     * @param $eventId
     *
     * @return array
     */
    public function fetchEventInformation($eventId = NULL)
    {
        $event = $this->client->get('/event/' . $eventId);

        return $event;
    }

    /**
     * Get the list of all the participants for the event.
     *
     * @param int $dateId
     *   Timestamp of the event starting time.
     * @param string|null $status
     *   Various status values to fetch appropriate participants list.
     *   Status can be: attended, did-not-attend, completed, left-early, banned
     *
     * @return array
     *   Array of all the participants.
     */
    public function fetchEventParticipants(int $dateId, $status = null) {
        return $this->client->get('/report/' . $dateId .'/participants');
    }

    /**
     * Get dummy data for the event.
     *
     * @return array[]
     */
    public function fetchDummySubscribersData() {
        return [
            [
                'uid' => 1,
                'email' => 'user1@gmail.com',
                'name' => 'User 1',
                'attended' => true,
                'status' => 'completed',
                'last_name' => 'User',
                'website' => 'google.com',
                'phone_number' => '123456789',
                'event_id' => '1234',
            ],
            [
                'uid' => 5,
                'email' => 'user2@gmail.com',
                'name' => 'User 2',
                'attended' => true,
                'status' => 'did-not-attended',
                'last_name' => 'User 2',
                'website' => 'fb.com',
                'phone_number' => '123456789',
                'event_id' => '1234',
            ],
            [
                'uid' => 7,
                'email' => 'user3@gmail.com',
                'name' => 'User 3',
                'attended' => true,
                'status' => 'left-early',
                'last_name' => 'User 3',
                'website' => 'tw.com',
                'phone_number' => '123456789',
                'event_id' => '5678',
            ],
            [
                'uid' => 9,
                'email' => 'user4@gmail.com',
                'name' => 'User 4',
                'attended' => true,
                'status' => 'completed',
                'last_name' => 'User 4',
                'website' => 'test.com',
                'phone_number' => '123456789',
                'event_id' => '9012'
            ],
        ];
    }

    /**
     * Get dummy data for events.
     *
     * @return \string[][]
     */
    public function fetchDummyEventsData() {
        return [
            [
                'id' => '1234',
                'name' => 'Test Event 1',
                'description' => '',
                'timestamp' => '1604052000',
            ],
            [
                'id' => '5678',
                'name' => 'Test Event 2',
                'description' => '',
                'timestamp' => '1604052000',
            ],
            [
                'id' => '9012',
                'name' => 'Test Event 3',
                'description' => '',
                'timestamp' => '1604052000',
            ],
        ];
    }
}
