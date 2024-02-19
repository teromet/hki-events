<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Utils;
use HkiEvents\Exceptions\HttpRequestFailedException;

/**
 * API class.
 *
 * Provides an access to a remote API
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
    private function get( $api_query ) {

        Utils::log( 'query-log', 'query: '.urldecode( $api_query ) );

        $response = wp_remote_get( $api_query, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            throw new HttpRequestFailedException( 'Http request to following endpoint failed: '.urldecode( $api_query ) );
        }

        $response_body = wp_remote_retrieve_body( $response );
        $result = json_decode( $response_body );

        return $result;

    }

    /**
     * Get all items
     * 
     * @param string $url
     * @param array $params
     * 
     * @return stdClass $result
     */
    public function get_all( $url, $params ) {

        $result = false;
        $api_query = sanitize_url( $url ).'?'.http_build_query( $params );

        try {
            $result = $this->get( $api_query );
        } catch ( HttpRequestFailedException $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }

        return $result;

    }

    /**
     * Get item by id
     * 
     * @param string $url
     * @param string $id
     * 
     * @return stdClass $result
     */
    public function get_by_id( $url, $id ) {

        $result = false;
        $api_query = sanitize_url( $url ).Utils::clean_string( $id );

        try {
            $result = $this->get( $api_query );
        } catch ( HttpRequestFailedException $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }

        return $result;

    }

}