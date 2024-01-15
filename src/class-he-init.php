<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\HE_API as Api;
use HkiEvents\HE_CPT as CPT;
use HkiEvents\HE_Event as Event;
use HkiEvents\HE_Settings_Page as Settings_Page;
use HkiEvents\HE_Utils as Utils;

class HE_Init {

    private $api;

    const CRON_HOOK = 'hki_events_cron';

    public function hook_into_wp() {
        add_action( 'init', array( $this, 'initialize' ) );
    }

    /**
     * Initialize classes and other WP hooks
     */
    public function initialize() {

        // Create custom post type
        $this->create_post_type();

        // Add cron schedule
        add_action( self::CRON_HOOK, array( $this, 'handle_cron' ) );
        $this->schedule_cron();

        // Add shortcode
        add_shortcode( 'hki_events', array( $this, 'shortcode' ) );

        // Add menu page
        $settings_page = new Settings_Page();

        // Test importing
        if( isset($_GET["hki_action"]) && trim($_GET["hki_action"]) == 'import_events') {
            $this->handle_cron();
        }

    }

    /**
     * Register a custom post type for events
     *
     */
    private function create_post_type() {

        $cpt_args = array(
            'type' => HE_POST_TYPE,
            'slug' => 'tapahtumat',
            'name' => __( 'Tapahtumat', 'hki_events' ),
            'singular_name' => __( 'Tapahtuma', 'hki_events' ),
            'is_public' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-groups'
        );

        $cpt = new CPT( $cpt_args );
        $cpt->register();

    }


    private function schedule_cron() {

        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
          wp_schedule_event( time(), 'daily', self::CRON_HOOK );
        }

    }

    public function handle_cron() {

        $this->api = new Api();

        $events = $this->api->get_upcoming_events();

        if( $events ) {
            foreach ( $events as $event ) {

                // Skip events with no start time
                if( !$event->start_time ) {
                    continue;
                }
                
                $this->create_event( $event );
            }
        }

    }

    /**
     * Create WP_Post for every event received
     *
     * @param array $event   Event data from Linked Events API
     */
    private function create_event( $event ) {

        $event_args = array(
            'title' => $event->name->fi,
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
            'description' => $event->description->fi,
            'recurring' => $event->super_event_type === 'recurring' ? true : false,
            'dates' => array( $event->start_time )
        );

        if( $event->super_event_type === 'recurring' ) {
            $event_args['dates'] = $this->get_sub_event_dates( $event->id );
        }

        $event_args['image_url'] = !empty( $event->images ) && !empty( $event->images[0]->url ) ? $event->images[0]->url : '';

        $event = new Event( $event_args );
        $post_id = $event->save();

    }

    /**
     * Get sub event dates of an recurring (super) event
     *
     * @param string $event_id   Linked Events API event id
     * @return array Array of sub event dates in ascending order
     */
    private function get_sub_event_dates( $event_id ) {

        $dates = array();

        $sub_events = $this->api->get_sub_events( $event_id );

        if( $sub_events ) {
            foreach ( $sub_events as $event ) {
                $dates[] = $event->start_time;
            }
        }

        return array_reverse( $dates );

    }


    public function shortcode() {

        $today = date('Ymd');
        $output = '';

        $meta_query = array(
                array(
                    'key'     	=> 'hki_event_start_time',
                    'compare' 	=> '>=',
                    'value'   	=> $today,
                    'type' 		=> 'DATE'
                )
            );

        $args = array(
            'post_type' 		=>  HE_POST_TYPE,
            'meta_key'     	    => 'hki_event_start_time',
            'orderby'           => 'meta_value',
            'order'             => 'ASC',
            'posts_per_page' 	=>  -1,
            'meta_query'		=> $meta_query
        );     
        
        $query = new \WP_Query($args);

        if( $query->have_posts() ):
        ob_start(); ?>

        <div class="hki-events-list">

            <?php while ( $query->have_posts() ) : $query->the_post();

            $post_id = get_the_ID();

            $image = get_post_thumbnail_id( $post_id );
            $image_size = 'full'; // (thumbnail, medium, large, full or custom size)
            $start_date = get_post_meta( $post_id, 'hki_event_start_time', true );
            $end_date = get_post_meta( $post_id, 'hki_event_end_time', true );  
            $start = date( 'd.m.Y k\l\o H.i', strtotime( $start_date ) );
            $end = date( 'd.m.Y k\l\o H.i', strtotime( $end_date ) );

            ?>
            <div class="hki-events-list-item">
            <?php if( has_post_thumbnail() ):
              echo '<div class="post-image">';
                echo wp_get_attachment_image( $image, 'large' );
              echo '</div>';
            endif; ?>
            <div class="post-content">
                <div class="post-content-wrapper">
                    <div class="post-date"><?php echo $start; ?></div>
                    <div class="post-title"><?php the_title(); ?></div>
                    <div class="post-button">
                        <a class="btn btn-news btn-primary" href="<?php the_permalink(); ?>">
                        <?php echo 'Lue lisää'; ?>
                        </a>
                    </div>
                </div>
                <div class="post-overlay"></div>
            </div>
          </div>
          <?php

        endwhile; ?>
        </div>
        <?php else: ?>
        <div class="no-posts">Ei tapahtumia</div>
        <?php
        endif; 
        wp_reset_postdata();
        ?> </div>
        <style>
            .hki-events-list {
                width: 100%;
                max-width: 100% !important;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
            }
            .hki-events-list:after {
                flex: 0 0 calc((100% - 2rem) / 3);
                content: '';
            }
            .hki-events-list-item {
                flex-basis: calc((100% - 2rem) / 3);
                position: relative;
                margin-bottom: 1rem;
            }
            .hki-events-list-item .post-content {
                position: absolute;
                top: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100%;
                width: 100%;
                color: #fff;
                font-weight: 500;
                font-size: 20px;
            }
            .hki-events-list-item .post-content-wrapper {
                z-index: 1;
                padding: 0 2rem;
                width: 100%;
            }
            .hki-events-list-item .post-date {
                font-size: 1rem;
            }
            .hki-events-list-item .post-title {
                font-size: 1.5rem;
            }
            .hki-events-list-item .post-button a {
                background: #fff;
                font-size: 1rem;
                padding: 0.75rem 2rem;
                text-decoration: none;
                margin-top: 4rem;
                display: inline-block;
            }
            .hki-events-list-item .post-overlay {
                height: 100%;
                width: 100%;
                position: absolute;
                top: 0;
                background: rgba(0, 0, 0, 0.4);
            }
            .hki-events-list-item .post-image {
                height: 100%;
            }
            .hki-events-list-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            @media only screen and (max-width: 1100px) {
                .hki-events-list-item {
                flex-basis: calc((100% - 1rem) / 2);
                margin-bottom: 1rem;
                }
            }
            @media only screen and (max-width: 600px) {
                .hki-events-list-item {
                flex-basis: 100%;
                margin-bottom: 1rem;
                }
            }
        </style>
        <?php
        $output = ob_get_clean();

        return $output;

    }
      
}