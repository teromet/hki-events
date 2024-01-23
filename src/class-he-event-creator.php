<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\HE_API as Api;
use HkiEvents\HE_Event as Event;

/**
 * HE_Event_Creator
 *
 * Creates Event objects from API Source
 *
 */
class HE_Event_Creator {

    /**
     * The API wrapper object
     *
     * @var object
     */
    private $api;

    function __construct( ) {

        $this->api = new Api();

    }

    /**
     * Get upcoming events from Linked Events API
     */
    public function get_events() {

        $events = $this->api->get_upcoming_events();

        if( $events ) {
            foreach ( $events as $event ) {
                // Skip events with no start time and test events
                if( !$event->start_time || str_contains( strtolower( $event->name->fi ), 'testitapahtuma' ) ) {
                    continue;
                }      
                $this->create_event( $event );
            }
        }

    }

    /**
     * Create WP_Post for every event received
     *
     * @param object $event   Event data from Linked Events API
     */
    private function create_event( $event ) {

        $dates =  $event->super_event_type === 'recurring' ? $this->get_sub_event_dates( $event->id ) : array();

        $event = new Event( $event, $dates );
        $post_id = $event->save();

    }

    /**
     * Get sub event dates of an recurring (super) event
     *
     * @param string $event_id   Linked Events API event id
     * @return array Array of sub event dates in ascending order
     */
    private function get_sub_event_dates( $event_id ) {

        $dates = array();

        $sub_events = $this->api->get_sub_events( $event_id );

        if( $sub_events ) {
            foreach ( $sub_events as $event ) {
                $dates[] = $event->start_time;
            }
        }

        return array_reverse( $dates );

    }

}