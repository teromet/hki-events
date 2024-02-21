<?php

namespace HkiEvents\Event;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Api\ApiInterface;
use HkiEvents\Keywords;

/**
 * EventController class.
 *
 * TODO: Refactor the keyword cache functionality etc.
 * 
 */
class EventController {

    /**
     * ApiInterface object
     *
     * @var ApiInterface
     */
    private $api;

    /**
     * Keywords that has been saved to a JSON file
     *
     * @var array
     */
    private $keywords;

    /**
     * Keywords that appear in the query results for the first time
     *
     * @var array
     */
    private $new_keywords = array();


    function __construct( ) {

        $this->api = new ApiInterface();
        $this->keywords = $this->get_keywords_json();

    }

    /**
     * Get upcoming events from Linked Events API
     */
    public function get_events() {

        $options = $this->get_user_options();
        $events = $this->api->set_options( $options )->get_events();

        if ( $events ) {
            
            foreach ( $events as $event ) {
                // Skip events with no start time and test events
                if ( ! $event->start_time || str_contains( strtolower( $event->name->fi ), 'testitapahtuma' ) ) {
                    continue;
                }      
                $this->create_event( $event );
            }

            $this->save_new_keywords();

            update_option( 'hki_events_last_fetched', date( 'Y-m-d' ) );

        }

    }

    /**
     * Create WP_Post for event
     *
     * @param object $event   Event data from Linked Events API
     */
    private function create_event( $event ) {

        if ( $event->name && ( $event->name->fi || $event->name->en )  ) {

            $keywords = $this->get_event_keywords( $event );

            $event = new Event( $event, $keywords );
            $post_id = $event->save();

        }

    }

    /**
     * Get event keywords
     *
     * @param object $event Event data from Linked Events API
     * 
     * @return array $keywords
     */
    private function get_event_keywords( $event ) {

        $keywords = array();

        if ( ! empty( $event->keywords ) ) {

            foreach ( $event->keywords as $keyword ) {

                $keyword_id = basename( $keyword->{'@id'} );

                if ( str_contains( $keyword_id, 'yso' ) ) {  

                    $keyword_data =  $this->get_keyword_data( $keyword_id );
                    $keywords[] = $keyword_data;
                }

            }

        }

        return $keywords;

    }

    /**
     * Get keyword data (id, name) from API or file
     *
     * @param string $keyword_id Linked Events API keyword ID
     * 
     * @return array $keyword
     */
    private function get_keyword_data( $keyword_id ) {

        $keyword = $this->keyword_exists( $keyword_id, $this->keywords );

        if ( ! $keyword ) {

            $keyword_api = $this->api->get_keyword( $keyword_id );

            $keyword = array(
                'id' => $keyword_api->id,
                'name' => $keyword_api->name->fi
            );

            if ( ! $this->keyword_exists( $keyword['id'], $this->new_keywords ) ) {
                $this->new_keywords[] = $keyword;
            }

        }

        return $keyword;

    }

    /**
     * Check if the keyword ID is present in the given array
     *
     * @param string $keyword_id Linked Events API keyword ID
     * @param array array containing keywords (id, name)
     * 
     * @return array|null $keyword or null if not found
     */
    private function keyword_exists( $keyword_id, $keyword_arr ) {

        if ( ! empty( $keyword_arr ) ) {
            foreach ( $keyword_arr as $keyword ) {
                if ( $keyword['id'] && $keyword['id'] === $keyword_id ) {
                    return $keyword;
                }
            }
        }

        return null;

    }

    /**
     * Get keyword data from JSON file
     *
     * @return array|null Decoded keyword data or null
     */
    private function get_keywords_json() {

        if ( ! file_exists( HE_DIR . '/inc/keywords.json' ) ) {
            return null;
        }

        $keywords_json = file_get_contents( HE_DIR . '/inc/keywords.json' ); 
        $keywords_data = json_decode( $keywords_json, true ); 

        return $keywords_data ? $keywords_data : null;

    }

    /**
     * Save keywords from API to a JSON file
     *
     */
    private function save_new_keywords() {

        $keywords_json = file_get_contents( HE_DIR . '/inc/keywords.json' ); 
        $keywords_data = json_decode( $keywords_json, true ); 

        if ( ! empty( $keywords_data ) ) {
            $keywords_data = array_merge( $keywords_data, $this->new_keywords );
        }
        else {
            $keywords_data = $this->new_keywords;
        }

        $json_string = json_encode( $keywords_data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT );

        $fp = fopen( HE_DIR . '/inc/keywords.json', 'w' );
        fwrite( $fp, $json_string );
        fclose( $fp );

    }

    /**
     * Get options from db
     * 
     * @return array $options 
     */
    private function get_user_options() {

        $time_span              = get_option( 'hki_events_time_span' );
        $free_only              = get_option( 'hki_events_free_only' );
        $last_fetched           = get_option( 'hki_events_last_fetched' );
        $categories             = get_option( 'hki_events_categories' );
        $demographic_groups     = get_option( 'hki_events_demographic_groups' );

        $options = array(
            'start_time'            => 'today',
            'time_span'             => $time_span,
            'last_modified_since'   => $last_fetched,
            'bool_options'          => array(
                'hide_recurring_children' => true,
                'is_free'                 => boolval( $free_only )
            )
        );

        if ( file_exists( HE_DIR . '/inc/keyword-groups.json' ) ) {

            $keywords_json = file_get_contents( HE_DIR . '/inc/keyword-groups.json' ); 
            $keywords_groups = json_decode( $keywords_json, true ); 

            $keywords = new Keywords( $keywords_groups );

            $keywords->set_keywords( $categories );
            $keywords->set_ignored_keywords( $demographic_groups );

            $options['keywords'] = $keywords->get_keywords();
            $options['ignored_keywords'] = $keywords->get_ignored_keywords();
            
        }

        return $options;

    }

}