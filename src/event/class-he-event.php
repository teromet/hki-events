<?php

namespace HkiEvents\Event;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Utils;
use HkiEvents\Exceptions\EventUpdateException;

/**
 * Event class.
 *
 * Wrapper for single hki_event post
 * 
 */
class Event {

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


    function __construct( \stdClass $event, $keywords = array() ) {

        $this->id               = $event->id;
        $this->name             = $event->name->fi ? $event->name->fi : $event->name->en;
        $this->start_time       = strtotime( $event->start_time ) ? $event->start_time : '';
        $this->end_time         = strtotime( $event->end_time ) ? $event->end_time : '';
        $this->description      = $event->description->fi;
        $this->recurring        = $event->super_event_type === 'recurring' ? true : false;
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

        $existing_id = $this->event_exists();
        $this->post_id = wp_insert_post( $this->props_to_args( $existing_id ), true );

        if ( ! is_wp_error( $this->post_id ) && $this->post_id > 0 ) {

            $this->add_id();

            if ( ! empty ( $this->start_time ) ) {
                $this->add_start_time();
            }
            if ( ! empty ( $this->end_time ) ) {
                $this->add_end_time();
            }
            if ( ! empty ( $this->image_url ) ) {
                $this->add_image_url();
            }
            if ( ! empty ( $this->image_alt_text ) ) {
                $this->add_image_alt_text();
            }

            $this->add_tags();

        }

        return $this->post_id;

    }

    /**
     * Add Linked Events id
     *
     */
    private function add_id() {

        try {
            $this->update_event_meta( 'hki_event_linked_events_id', $this->id );
        } catch ( EventUpdateException $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }
        
    }

    /**
     * Add post image url
     *
     */
    private function add_image_url() {

        try {
            $this->update_event_meta( 'hki_event_image_url', $this->image_url );
        } catch ( EventUpdateException $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }
        
    }

    /**
     * Add post image alt text
     *
     */
    private function add_image_alt_text() {

        try {
            $this->update_event_meta( 'hki_event_image_alt_text', $this->image_alt_text );
        } catch ( EventUpdateException $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }
        
    }

    /**
     * Add event start time
     * 
     */
    private function add_start_time() {

        try {
            $this->update_event_meta( 'hki_event_start_time', $this->start_time );
        } catch ( EventUpdateException $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }

    }

    /**
     * Add event end time
     * 
     */
    private function add_end_time() {

        try {
            $this->update_event_meta( 'hki_event_end_time', $this->end_time );
        } catch ( EventUpdateException $e ) {
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
                    if ( is_array( $v ) && array_key_exists( 'name', $v ) ) {
                        return $v['name'];
                    }     
                }, array_values( $this->keywords ) );
    
            try {
                $this->set_event_tags( $tags );
            } catch ( EventUpdateException $e ) {
                Utils::log( 'error', 'Caught exception: ' . $e->getMessage() );
            }

        }

    }

    /**
     * Wrapper for wp_set_post_terms
     * 
     * @throws EventUpdateException If wp_set_post_terms returns WP_Error
     * 
     * @param string|array $tags
     * 
     * @return array|false Array of term taxonomy IDs or false. 
     * 
     */
    private function set_event_tags( $tags ) {

        $results = wp_set_post_terms( $this->post_id, $tags, HE_TAXONOMY, false );
    
        if ( is_a( $results, 'WP_Error' ) ) {
            throw new EventUpdateException( 'Setting event tags of post ' . $this->post_id . ' failed: ' . $results->get_error_message() );
        }
    
        return $results;

    }

    /**
     * Wrapper for update_post_meta
     * 
     * @throws EventUpdateException If update_post_meta returns false
     * 
     * @param $meta_name metadata key
     * @param $meta_value metadata value
     * 
     * @return int|bool Meta ID if the key didn’t exist, true on successful update
     * 
     */
    private function update_event_meta( $meta_name, $meta_value ) {

        $results = update_post_meta( $this->post_id, $meta_name, $meta_value );
    
        if ( $results === false ) {
            throw new EventUpdateException( 'Updating meta field ' . $meta_name . ' of post ' . $this->post_id . ' failed.'  );
        }
    
        return $results;

    }

    /**
     * Check if post with Linked Events id exists. Return the id or false
     * 
     * @return integer|false
     */
    private function event_exists() {

        $query_args = array(
            'post_type'  => HE_POST_TYPE,
            'posts_per_page' => 1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'   => 'hki_event_linked_events_id',
                    'value' => $this->id
                )
            ),
        );

        $posts = get_posts( $query_args );

        if( ! empty ( $posts ) &&  $posts[0]->post_title == $this->name ) {
            return $posts[0]->ID;
        }

        return false;

    }
    
    /**
     * Create a WP_Post args array
     * 
     * @param int $post_id
     * 
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