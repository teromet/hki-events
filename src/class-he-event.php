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

    private $title;
    private $start_time;
    private $end_time;
    private $image_url;
    private $description;
    private $recurring;
    private $dates;

    function __construct( $args ) {

        $this->title = $args['title'];
        $this->start_time = $args['start_time'];
        $this->end_time = $args['end_time'];
        $this->image_url = $args['image_url'];
        $this->description = $args['description'];
        $this->recurring = $args['recurring'];
        $this->dates = $args['dates'];

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

        $existing_id = post_exists( $this->title, '', '', HE_POST_TYPE );

        $post_data = array(
            'ID' => $existing_id,
            'post_title' => $this->title,
            'post_content' => $this->description,
            'post_type' => HE_POST_TYPE,
            'post_status' => 'publish'
        );

        $post_id = wp_insert_post( $post_data );

        if( !is_wp_error( $post_id ) && $post_id > 0 ) {

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

        $current_image_url = get_post_meta( $post_id, 'hki_event_image_url', true );

        if ( empty ( $current_image_url ) || $current_image_url !== $this->image_url ) {
            Utils::upload_thumbnail_from_url( $post_id, $this->image_url );
        }

        add_post_meta( $post_id, 'hki_event_image_url', $this->image_url );

    }

    /**
     * Add or update event dates
     * 
     * @param int $post_id
     */
    private function add_event_dates( $post_id ) {

        if ( !empty( $this->start_time ) && strtotime( $this->start_time ) ) {
            update_field( 'hki_event_start_time', $this->start_time, $post_id );
        }
        if ( !empty( $this->end_time ) && strtotime( $this->end_time ) ) {
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

}