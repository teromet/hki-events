<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\HE_Utils as Utils;

/**
 * HE_API
 *
 * Provides an access to Linked Events API 
 *
 */
class HE_API {

    const HE_API_URL = 'https://api.hel.fi/linkedevents/v1/event/?';


    /**
     * Get events. Return an array containing result meta data and events or WP_Error
     * 
     * @param array $params API params
     * @param bool $meta_data Include meta data in the results
     *
     * @return array|WP_Error
     * 
     */
    private function get_events( $params, $meta_data = true ) {

        $api_query = self::HE_API_URL.http_build_query( $params );

        Utils::log( 'query-log', 'query: '.urldecode( self::HE_API_URL.http_build_query( $params ) ) );

        $response = wp_remote_get( $api_query, ['timeout' => 10] );

        if( is_wp_error( $response ) ) {
            return new \WP_Error( 'api error', __( 'API Error', 'hki_events' ) );
        }

        $response_body = wp_remote_retrieve_body( $response );
        $results = json_decode( $response_body );

        if( ! $meta_data ) {
            $results = $results->data;
        }

        return $results;

    }

    /**
     * Get upcoming events from the next month. Return array of events or WP_Error
     *
     * @return array|WP_Error
     * 
     */
    public function get_upcoming_events() {

        require_once( HE_DIR . '/inc/api-config.php' );

        $page = 1;
        $events = array();

        $dt = new \DateTime( 'now', new \DateTimeZone( 'Europe/Helsinki' ) );
        $time = $dt->getTimestamp();
        $end = date( 'Y-m-d', strtotime( '+1 month', $time ) );

        // API Params
        $params = array( 
            'is_free' => 'true',
            'keyword' => implode( ",", $event_categories ),
            'keyword!' => implode( ",", $skip_categories ),
            'hide_recurring_children' => 'true',
            'start' => get_option( 'hki_events_api_start_date' ) ? get_option( 'hki_events_api_start_date' ) : 'today',
            'sort' => 'end_time',
            'end' => $end,
            'page' => $page
        );

        do {

            $results = $this->get_events( $params );

            if( ! empty( $results ) && ! empty( $results->data ) ) {
                $events = array_merge( $events, $results->data );
                $params['page'] = $params['page'] + 1;
            }


        } while ( $results->meta->next );

        return $events;

    }

    /**
     * Get all subevents for specific superevent. Return array of events or WP_Error
     * 
     * @param string $event_id
     * 
     * @return array|WP_Error
     * 
     */
    public function get_sub_events( $event_id ) {

        // API Params
        $params = array( 
            'super_event' => $event_id
        );

        $events = $this->get_events( $params, false );

        return $events;

    }

}