<?php

namespace HkiEvents\Api;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Api\API;
use HkiEvents\Api\QueryBuilder;
use HkiEvents\Api\ApiOptions;
use HkiEvents\Utils;

use HkiEvents\Exceptions\HttpRequestFailedException;

/**
 * ApiInterface class.
 * 
 * Provides an access to Linked Events API
 *
 */
class ApiInterface {

    /**
     * API object
     *
     * @var API
     */
    private $api;

    /**
     * Array of user options
     *
     * @var array
     */
    private $options;

    /**
     * Linked Events url
     *
     * @var string
     */
    const API_V1_URL = 'https://api.hel.fi/linkedevents/v1/';

    /**
     * ApiInterface constructor.
     *
     */
    function __construct() {

        $this->api = new API();

    }

    public function set_options( $options ) {

        $this->options = $options;

        return $this;

    }
    
    /**
     * Get events. Return array of events
     *
     * @return \stdClass[] 
     * 
     */
    public function get_events() {

        $events = array();

        $options = new ApiOptions( $this->options );
        $query   = new QueryBuilder( self::API_V1_URL );

        $query = $query->endpoint( 'event' )->with_params( $options->get_params() );

        do {
            try {
                $result = $this->api->get( $query->get_query() );
            } catch ( HttpRequestFailedException $e ) {
                Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
            }

            if ( ! empty( $result ) && ! empty( $result->data ) ) {
                $events = array_merge( $events, $result->data );
                $query->next_page();
            }
        } while ( $result->meta->next );

        return $events;

    }

    /**
     * Get keyword
     * 
     * @param string $keyword_id Linked Events API Keyword ID
     * 
     * @return object|false keyword data or false on error
     * 
     */
    public function get_keyword( $keyword_id ) {

        $result = false;

        // Match Linked Events id format
        if( ! preg_match('/^[a-z]+:[a-z0-9]+$/', $keyword_id ) ) {
            return false;
        }

        $query = new QueryBuilder( self::API_V1_URL );
        $query = $query->endpoint( 'keyword' )->with_id( $keyword_id );

        try {
            $result = $this->api->get( $query->get_query() );
        } catch ( HttpRequestFailedException $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }

        return $result;

    }

}