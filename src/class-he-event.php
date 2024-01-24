<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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


    function __construct( $event, $dates ) {

        $this->id               = $event->id;
        $this->name             = $event->name->fi;
        $this->start_time       = $event->start_time;
        $this->end_time         = $event->end_time;
        $this->description      = $event->description->fi;
        $this->recurring        = $event->super_event_type === 'recurring' ? true : false;
        $this->dates            = $dates;
        $this->image_url        = !empty( $event->images ) && !empty( $event->images[0]->url ) ?  $event->images[0]->url : '';
        $this->image_alt_text   = !empty( $event->images ) && !empty( $event->images[0]->alt_text ) ?  $event->images[0]->alt_text : '';

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

        $post_id = wp_insert_post( $this->props_to_args( $existing_id ) );

        if( ! is_wp_error( $post_id ) && $post_id > 0 ) {

            $this->add_thumbnail( $post_id );
            $this->add_event_dates( $post_id );
        
        }

        return $post_id;

    }

    /**
     * Add post thumbnail from url
     * 
     * @param int $post_id
     */
    private function add_thumbnail( $post_id ) {

        if ( ! empty( $this->image_url ) ) {
            update_field( 'hki_event_image_url', $this->image_url, $post_id );
        }
        if ( ! empty( $this->image_alt_text ) ) {
            update_field( 'hki_event_image_alt_text', $this->image_alt_text, $post_id );
        }
        
    }

    /**
     * Add or update event dates
     * 
     * @param int $post_id
     */
    private function add_event_dates( $post_id ) {

        if ( ! empty( $this->start_time ) && strtotime( $this->start_time ) ) {
            update_field( 'hki_event_start_time', $this->start_time, $post_id );
        }
        if ( ! empty( $this->end_time ) && strtotime( $this->end_time ) ) {
            update_field( 'hki_event_end_time', $this->end_time, $post_id );
        }

        if ( $this->recurring && !empty( $this->dates ) ) {

            $dates = array_filter( $this->dates, function( $v ) {
                return !empty( $v ) && strtotime( $v );
            } );

            $date_formatted = implode( ', ', array_map(
                function( $v ) { 
                    return date( 'j.n.Y', strtotime( $v ) );
                }, array_values( $dates ) )
            );

            update_field( 'hki_event_dates', $date_formatted, $post_id );

        }

    }

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