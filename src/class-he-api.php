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

    const HE_API_URL = 'https://api.hel.fi/linkedevents/v1/';
    
    /**
     * Get events. Return an object containing events and metadata or only array of events
     * 
     * @param array $params API params
     * @param bool $meta_data Include meta data in the results
     * @return array|object
     * @throws Exception If wp_remote_get returns WP_Error
     * 
     */
    private function get_events( $params, $meta_data = true ) {

        $api_query = self::HE_API_URL.'event/?'.http_build_query( $params );

        Utils::log( 'query-log', 'query: '.urldecode( $api_query ) );

        $response = wp_remote_get( $api_query, ['timeout' => 10] );

        if ( is_wp_error( $response ) ) {
            throw new \Exception();
        }

        $response_body = wp_remote_retrieve_body( $response );
        $results = json_decode( $response_body );

        if ( ! $meta_data ) {
            $results = $results->data;
        }

        return $results;

    }

    /**
     * Get upcoming events from the next month. Return array of events
     *
     * @return array
     * 
     */
    public function get_upcoming_events() {

        $events = array();
        $params = $this->get_api_params();

        do {

            try {
                $results = $this->get_events( $params );

                if ( ! empty( $results ) && ! empty( $results->data ) ) {
                    $events = array_merge( $events, $results->data );
                    $params['page'] = $params['page'] + 1;
                }

            } catch ( \Exception $e ) {
                Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
            }

        } while ( $results->meta->next );

        return $events;

    }

    /**
     * Get all subevents for specific superevent. Return array of events
     * 
     * @param string $event_id
     * @return array
     * 
     */
    public function get_sub_events( $event_id ) {

        $events = array();

        // API Params
        $params = array( 
            'super_event' => $event_id
        );

        try {
            $events = $this->get_events( $params, false );
        } catch ( \Exception $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }

        return $events;

    }

    /**
     * Get keyword
     * 
     * @param string $keyword_id Linked Events API Keyword ID
     * @return object keyword data
     * @throws Exception If wp_remote_get returns WP_Error
     * 
     */
    public function get_keyword( $keyword_id ) {

        $api_query = self::HE_API_URL.'keyword/'.$keyword_id;

        Utils::log( 'query-log', 'query: '.urldecode( $api_query ) );

        $response = wp_remote_get( $api_query, ['timeout' => 10] );

        if ( is_wp_error( $response ) ) {
            throw new \Exception();
        }

        $response_body = wp_remote_retrieve_body( $response );
        $keyword = json_decode( $response_body );

        return $keyword;

    }

    /**
     * Create an array for Linked Events API params
     * 
     * @return array api params
     * 
     */
    private function get_api_params() {

        require_once( HE_DIR . '/inc/api-config.php' );

        $page = 1;
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

        return $params;

    }

}