<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\HE_CPT as CPT;
use HkiEvents\HE_Event_Creator as Event_Creator;
use HkiEvents\HE_Settings_Page as Settings_Page;

class HE_Init {

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

        // Add assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Add shortcode
        add_shortcode( 'hki_events', array( $this, 'shortcode' ) );

        // Add menu page
        $settings_page = new Settings_Page();

        // Test importing
        if( isset($_GET["hki_action"]) && trim($_GET["hki_action"]) == 'import_events') {
            $this->handle_cron();
        }

        add_filter( 'post_thumbnail_html', array( $this, 'filter_event_thumbnail' ), 10, 3 );

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

        $event_creator = new Event_Creator();
        $event_creator->get_events();

    }

    public function filter_event_thumbnail( $html, $post_id, $thumbnail_id ) {

        if ( ! $thumbnail_id ) {

            $src = get_post_meta( $post_id, 'hki_event_image_url', true );
            $alt = get_post_meta( $post_id, 'hki_event_image_alt_text', true );

            $alt_str = !empty ( $alt ) ? 'alt="'.$alt.'"' : '';

            if ( $src ) {
                $html = '<img src="' . $src . '" '.$alt_str.'>';
            }
        }

        return $html;

    }

    /**
     * Enqueue plugin stylesheet
     */
    public function enqueue_assets() {

        global $post;

        if ( has_shortcode( $post->post_content, HE_SHORTCODE ) )  {
            wp_enqueue_style( 'hki_events_style', HE_URL . '/assets/style.css', array(), '1.0', 'all' );
        }

    }

    /**
     * A callback function for hki_events shortcode. Outputs a list of events.
     * 
     * @return string html
     */
    public function shortcode() {

        $output = '';

        $query = $this->get_event_query();

        if( $query->have_posts() ):
            ob_start();
            require ( HE_DIR . '/template-parts/event-list.php' );
        else:
            echo '<div class="no-posts">'.__('Lue lisää', 'hki_events' ).'</div>';
        endif; 

        wp_reset_postdata();

        ?></div><?php

        $output = ob_get_clean();

        return $output;

    }

    /**
     * Get WP_Query for events
     * 
     * @return WP_Query
     */
    private function get_event_query() {

        $today = date( 'Ymd' );

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
        
        return new \WP_Query( $args );

    }
      
}