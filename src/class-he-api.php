<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * HE_API
 *
 * Provides an access to Helsinki Events API
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
    public function get_events() {

        // API Params
        $api_params = array( 
            'is_free' => 'true',
            'keyword' => 'yso:p27962',
            'start' => get_option( 'hki_events_api_start_date' ) ? get_option( 'hki_events_api_start_date' ) : 'today'
        );

        $response = wp_remote_get( self::HE_API_URL.http_build_query($api_params), ['timeout' => 10] );
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

}