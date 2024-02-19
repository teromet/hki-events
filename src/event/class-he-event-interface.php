<?php

namespace HkiEvents\Event;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\API;
use HkiEvents\LinkedEventsParams;
use HkiEvents\LinkedEventsKeywords;
use HkiEvents\Utils;

/**
 * EventInterface class.
 *
 */
class EventInterface {

    /**
     * API object
     *
     * @var API
     */
    private $api;

    /**
     * Linked Events events url
     *
     * @var string
     */
    const EVENTS_URL = 'https://api.hel.fi/linkedevents/v1/event/';

    /**
     * Linked Events eeyword url
     *
     * @var string
     */
    const KEYWORD_URL = 'https://api.hel.fi/linkedevents/v1/keyword/';

    /**
     * EventInterface constructor.
     *
     */
    function __construct() {

        $this->api = new API();

    }
    
    /**
     * Get events. Return array of events
     *
     * @return \stdClass[] 
     * 
     */
    public function get_events() {

        $options = $this->get_user_options();
        $keywords = $this->get_keyword_params();

        $params = new LinkedEventsParams( $keywords, $options );

        $events = array();

        do {
            $result = $this->api->get_all( self::EVENTS_URL, $params );

            if ( ! empty( $result ) && ! empty( $result->data ) ) {
                $events = array_merge( $events, $result->data );
                $params->next_page();
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

        // Match Linked Events id format
        if( ! preg_match('/^[a-z]+:[a-z0-9]+$/', $keyword_id ) ) {
            return false;
        }

        $keyword = $this->api->get_by_id( self::KEYWORD_URL, $keyword_id );

        return $keyword;

    }

    /**
     * Get options from db
     * 
     * @return array $options 
     */
    private function get_user_options() {

        $time_span          = get_option( 'hki_events_time_span' );
        $free_only          = get_option( 'hki_events_free_only' );
        $last_fetched       = get_option( 'hki_events_last_fetched' );

        $options = array(
            'time_span' => $time_span,
            'free_only' => boolval( $free_only ),
            'last_fetched' => $last_fetched 
        );

        return $options;

    }


    /**
     * Get keyword URL params 
     * 
     * @return array $params
     */
    private function get_keyword_params() {

        $categories         = get_option( 'hki_events_categories' );
        $demographic_groups = get_option( 'hki_events_demographic_groups' );

        $keywords = new LinkedEventsKeywords();

        $keywords->set_keywords( $categories );
        $keywords->set_ignored_keywords( $demographic_groups );

        $params = $keywords->get_params();

        return $params;

    }


}