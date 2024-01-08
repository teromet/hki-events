<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


use HkiEvents\HE_Utils as Utils;


/**
 * HE_API
 *
 * Provides an access to Helsinki Linked Events API
 *
 */
class HE_API {

    const HE_API_URL = 'https://api.hel.fi/linkedevents/v1/event/?';


    /**
     * Get events. Return array of events or WP_Error
     *
     * @return array|WP_Error
     * 
     */
    private function get_events( $params ) {

        Utils::log( 'query-log', 'query: '.self::HE_API_URL.http_build_query($params) );

        $response = wp_remote_get( self::HE_API_URL.http_build_query($params), ['timeout' => 10] );
        $events = array();

        if( is_wp_error( $response ) ) {
            return new WP_Error( 'api error', __( 'API Error', 'hki_events' ) );
        }

        $response_body = wp_remote_retrieve_body( $response );
        $response_body = json_decode( $response_body );

        if( !empty( $response_body ) && !empty( $response_body->data ) ) {
            $events = $response_body->data;
        }

        return $events;

    }

    /**
     * Get upcoming events. Return array of events or WP_Error
     *
     * @return array|WP_Error
     * 
     */
    public function get_upcoming_events() {

        // API Params
        $params = array( 
            'is_free' => 'true',
            'keyword' => 'yso:p27962',
            'hide_recurring_children' => 'true',
            'start' => get_option( 'hki_events_api_start_date' ) ? get_option( 'hki_events_api_start_date' ) : 'today'
        );

        $events = $this->get_events( $params );

        return $events;

    }

    /**
     * Get all upcoming subevents for specific superevent. Return array of events or WP_Error
     * 
     * @param string $event_id
     * 
     * @return array|WP_Error
     * 
     */
    public function get_sub_events( $event_id ) {

        // API Params
        $params = array( 
            'super_event' => $event_id,
            'start' => get_option( 'hki_events_api_start_date' ) ? get_option( 'hki_events_api_start_date' ) : 'today'
        );

        $events = $this->get_events( $params );

        return $events;

    }

}