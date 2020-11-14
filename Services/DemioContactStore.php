<?php

namespace MauticPlugin\DemioBundle\Services;

use MauticPlugin\DemioBundle\Connection\ApiConsumer;

class DemioContactStore
{
    /**
     * @var ApiConsumer
     */
    private $apiConsumer;

    /**
     * The variable to hold the parameters for the next link if any.
     */
    private $nextPageParameters;

    private $channelId;

    public function __construct(
        ApiConsumer $apiConsumer
    ) {
        $this->apiConsumer   = $apiConsumer;
    }

    /**
     * Fetch the list of all the events for the account
     */
    public function getAllEvents() {
        $events = $this->apiConsumer->fetchEvents();
        if (!empty($events)) {
            return json_decode((string) $events, TRUE);
        }
        return [];
    }

    /**
     * Fetch the list of all the participants for the events.
     *
     * @param integer $dateId
     *   Date id for the event start date.
     * @param string|null $status
     *   Status of the event.
     * @return array
     */
    public function getAllParticipants(int $dateId, $status = null) {
        $processedData = [];
        $participants = $this->apiConsumer->fetchEventParticipants($dateId, $status);
        if (!empty($participants)) {
            $participants = json_decode((string) $participants);
            // Process and arrange data in proper format.
            foreach ($participants['participant'] as $participant) {
                $data = [
                    'email' => $participant->email,
                    'name' => $participant->name,
                    'attended' => $participant->attended,
                    'status' => $participant->status,
                ];
                $customFields = $participant->custom_fields;
                foreach ($customFields as $field) {
                    $data[$field->id] = $field->value;
                }
                $processedData[] = $data;
            }
            return $processedData;
        }
        return [];
    }


    /**
     * Get dummy data for the event.
     *
     * @return array[]
     */
    public function getDummySubscribersData() {
        return $this->apiConsumer->fetchDummySubscribersData();
    }

    /**
     * Get dummy data for events.
     *
     * @return \string[][]
     */
    public function getDummyEventsData() {
        $this->apiConsumer->fetchDummyEventsData();
    }
}
