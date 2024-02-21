<?php

namespace HkiEvents\Api;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Utils;
use HkiEvents\Exceptions\HttpRequestFailedException;

/**
 * API class.
 *
 * Provides HTTP request methods
 *
 */
class API {

    /**
     * Perform a GET request
     * 
     * @throws HttpRequestFailedException If wp_remote_get returns WP_Error
     * 
     * @param string $api_query
     * 
     * @return stdClass $result
     * 
     */
    public function get( string $api_query ) {

        Utils::log( 'query-log', 'query: '.urldecode( $api_query ) );

        $response = wp_remote_get( $api_query, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            throw new HttpRequestFailedException( 'Http request to following endpoint failed: '.urldecode( $api_query ) );
        }

        $response_body = wp_remote_retrieve_body( $response );
        $result = json_decode( $response_body );

        return $result;

    }

}