<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\HE_Utils as Utils;

/**
 * HE_Event
 *
 * Wrapper for single event
 * 
 */
class HE_Event {

    /**
     * The event's Linked Events API ID.
     *
     * @var string
     */
    private $id;

    /**
     * The event's post ID
     *
     * @var int
     */
    private $post_id;

    /**
     * The name of the event.
     *
     * @var string
     */
    private $name;
    
    /**
     * The start time of the event in ISO 8601 format.
     *
     * @var string
     */
    private $start_time;

    /**
     * The end time of the event in ISO 8601 format.
     *
     * @var string
     */
    private $end_time;
 
    /**
     * The description of the event.
     *
     * @var string
     */
    private $description;
 
    /**
     * If event is recurring or not.
     *
     * @var bool
     */
    private $recurring;

    /**
     * Event dates
     *
     * @var array
     */
    private $dates;

    /**
     * Event's Linked Events API keywords
     *
     * @var array
     */
    private $keywords;

    /**
     * Event image's (thumbnail) source URL
     *
     * @var string
     */
    private $image_url;

    /**
     * Alt text of the image
     *
     * @var string
     */
    private readonly string $image_alt_text;


    function __construct( \stdClass $event, $dates, $keywords = array() ) {

        $this->id               = $event->id;
        $this->name             = $event->name->fi ? $event->name->fi : $event->name->en;
        $this->start_time       = strtotime( $event->start_time ) ? $event->start_time : '';
        $this->end_time         = strtotime( $event->end_time ) ? $event->end_time : '';
        $this->description      = $event->description->fi;
        $this->recurring        = $event->super_event_type === 'recurring' ? true : false;
        $this->dates            = $dates;
        $this->keywords         = $keywords;
        $this->image_url        = ! empty( $event->images ) && ! empty( $event->images[0]->url ) ?  $event->images[0]->url : '';
        $this->image_alt_text   = ! empty( $event->images ) && ! empty( $event->images[0]->alt_text ) ?  $event->images[0]->alt_text : '';

    }

    /**
     * Save new post. Update if exists.
     * 
     * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure
     */
    public function save() {

        if ( ! function_exists( 'post_exists' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/post.php' );
        }

        $existing_id = post_exists( $this->name, '', '', HE_POST_TYPE );

        $this->post_id = wp_insert_post( $this->props_to_args( $existing_id ), true );

        if( ! is_wp_error( $this->post_id ) && $this->post_id > 0 ) {

            $this->add_thumbnail();
            $this->add_dates();
            $this->add_tags();

        }

        return $this->post_id;

    }

    /**
     * Add post thumbnail from url
     *
     */
    private function add_thumbnail() {

        try {
            $this->update_event_meta( 'hki_event_image_url', $this->image_url );
            $this->update_event_meta( 'hki_event_image_alt_text', $this->image_alt_text );
        } catch ( \Exception $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }
        
    }

    /**
     * Add or update event dates
     * 
     */
    private function add_dates() {

        try {

            $this->update_event_meta( 'hki_event_start_time', $this->start_time );
            $this->update_event_meta( 'hki_event_end_time', $this->end_time );

            if ( $this->recurring && !empty( $this->dates ) ) {

                $dates = array_filter( $this->dates, function( $v ) {
                    return ! empty( $v ) && strtotime( $v );
                } );
    
                $date_formatted = implode( ', ', array_map(
                    function( $v ) { 
                        return date( 'j.n.Y', strtotime( $v ) );
                    }, array_values( $dates ) )
                );
    
                $this->update_event_meta( 'hki_event_dates', $date_formatted );
    
            }

        } catch ( \Exception $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }

    }

    /**
     * Add event post tags
     * 
     */
    private function add_tags() {

        if ( ! empty( $this->keywords ) ) {

            $tags = array_map(
                function( $v ) { 
                    if( is_array( $v ) && array_key_exists( 'name', $v ) ) {
                        return $v['name'];
                    }     
                }, array_values( $this->keywords ) );
    
            wp_set_post_tags( $this->post_id, $tags, false );

        }

    }

    /**
     * Wrapper for update_post_meta
     * 
     * @param $meta_name metadata key
     * @param $meta_value metadata value
     * @return int|bool Meta ID if the key didn’t exist, true on successful update, false on failure
     */
    private function update_event_meta( $meta_name, $meta_value ) {

        $results = update_post_meta( $this->post_id, $meta_name, $meta_value );
    
        if ( is_a( $results, 'WP_Error' ) ) {
            throw new \Exception();
        }
    
        return $results;

    }
    
    /**
     * Create a WP_Post args array
     * 
     * @param int $post_id
     * @return array post args
     */
    private function props_to_args( $post_id ) {

        return array(
            'ID' => $post_id,
            'post_title' => $this->name,
            'post_content' => $this->description,
            'post_type' => HE_POST_TYPE,
            'post_status' => 'publish'
        );

    }

}