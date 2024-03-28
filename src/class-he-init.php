<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\CPT;
use HkiEvents\Event\EventController;
use HkiEvents\Admin\SettingsPage;

/**
 * Init class.
 *
 * Enabling plugin functionality via WordPress hook system. Registering custom post types and taxonomies.
 * 
 */
class Init {

    const CRON_HOOK = 'hki_events_cron';

    public function hook_into_wp() {
        add_action( 'init', array( $this, 'initialize' ) );
    }

    public function initialize() {

        // Create custom post type
        $this->create_post_type();
        // Create custom taxonomy
        $this->create_taxonomies();

        // Register custom fields to REST Api
        add_action( 'rest_api_init', array( $this, 'register_rest_meta_fields' ) );

        // Add cron schedule
        add_action( self::CRON_HOOK, array( $this, 'handle_cron' ) );
        $this->schedule_cron();
        
        // Add assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Add shortcode
        add_shortcode( 'hki_events', array( $this, 'shortcode' ) );

        // Add footer
        add_action( 'wp_footer', array( $this, 'hki_events_footer' ), 100 );

        // Add menu page
        $settings_page = new SettingsPage();

        add_filter( 'post_thumbnail_html', array( $this, 'filter_event_thumbnail' ), 10, 3 );
        add_filter( 'rest_hki_event_query', array( $this, 'filter_hki_event_rest_query' ), 999, 2 );

    }

    /**
     * Register a custom post type for events
     *
     */
    private function create_post_type() {

        $cpt_args = array(
            'type' => HE_POST_TYPE,
            'slug' => 'tapahtumat',
            'name' => __( 'Events', 'hki_events' ),
            'singular_name' => __( 'Event', 'hki_events' ),
            'is_public' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-groups',
            'taxonomies' => array( HE_TAXONOMY )
        );

        $cpt = new CPT( $cpt_args );
        $cpt->register();

    }

    /**
     * Register a custom taxonomy for events
     *
     */
    public function create_taxonomies() {

        $labels = array(
            'name' => _x( 'Tags', 'taxonomy general name' ),
            'singular_name' => _x( 'Tag', 'taxonomy singular name' ),
            'search_items' =>  __( 'Search Tags' ),
            'popular_items' => __( 'Popular Tags' ),
            'all_items' => __( 'All Tags' ),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __( 'Edit Tag' ), 
            'update_item' => __( 'Update Tag' ),
            'add_new_item' => __( 'Add New Tag' ),
            'new_item_name' => __( 'New Tag Name' ),
            'separate_items_with_commas' => __( 'Separate tags with commas' ),
            'add_or_remove_items' => __( 'Add or remove tags' ),
            'choose_from_most_used' => __( 'Choose from the most used tags' ),
            'menu_name' => __( 'Tags' ),
        ); 

        register_taxonomy( HE_TAXONOMY, HE_POST_TYPE, array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array( 'slug' => 'tag' )
        ) );
    
    }

    /**
     * Adds meta fields to rest api 'hki_event' endpoint
     */
    public function register_rest_meta_fields() {

        register_rest_field( HE_POST_TYPE,
            'hki_event_image_url',
            array(
                'get_callback'      => array( $this, 'post_meta_callback' ),
                'update_callback'   => null,
                'schema'            => null,
            )
        );

        register_rest_field( HE_POST_TYPE,
            'hki_event_image_alt_text',
            array(
                'get_callback'      => array( $this, 'post_meta_callback' ),
                'update_callback'   => null,
                'schema'            => null,
            )
        );

        register_rest_field( HE_POST_TYPE,
            'hki_event_start_time',
            array(
                'get_callback'      => array( $this, 'post_meta_callback' ),
                'update_callback'   => null,
                'schema'            => null,
            )
        );

        register_rest_field( HE_POST_TYPE,
        'hki_event_end_time',
            array(
                'get_callback'      => array( $this, 'post_meta_callback' ),
                'update_callback'   => null,
                'schema'            => null,
            )
        );

    }

    /**
     * Return hki_event meta object.
     *
     * @param array $post WP_Post
     * @param string $field_name Registered custom field name
     *
     * @return mixed
     */
    public function post_meta_callback( $post, $field_name ) {
        return get_post_meta( $post['id'], $field_name, true );
    }

    /**
     * Schedule the hki_events_cron event.
     * 
     * If the import_schedule option is changed, unschedule the previous event and schedule a new.
     * If the import_schedule option is empty or is not supported (e.g. has value 'off'), unschedule the event and exit.
     * 
     */
    private function schedule_cron() {

        $import_schedule        = get_option( 'hki_events_import_schedule' );
        $supported_schedules    = wp_get_schedules();

        if ( empty ( $import_schedule ) || ! array_key_exists( $import_schedule, $supported_schedules ) ) {
            $this->unschedule_cron();
            return;
        }

        $scheduled_event = wp_get_scheduled_event( self::CRON_HOOK );

        if( $scheduled_event && $scheduled_event->schedule !== $import_schedule ) {
            $this->unschedule_cron();
        }

        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
          wp_schedule_event( time(), $import_schedule , self::CRON_HOOK );
        }

    }

    /**
     * Unschedule the cron event
     */
    private function unschedule_cron() {
        $timestamp = wp_next_scheduled( self::CRON_HOOK );
        wp_unschedule_event( $timestamp, self::CRON_HOOK );
    }

    /**
     * Cron hook callback function
     */
    public function handle_cron() {

        $event_controller = new EventController();
        $event_controller->get_events();

        flush_rewrite_rules();

    }

    public function filter_hki_event_rest_query( $args ) {

        $today = date( 'Ymd' );

        $meta_query = array(
            array(
                'key'     	=> 'hki_event_end_time',
                'compare' 	=> '>=',
                'value'   	=> $today,
                'type' 		=> 'DATE'
            )
        );

        if ( isset( $args['meta_query'] ) ) {
            $args['meta_query'][] = $meta_query;
        } else {
            $args['meta_query'] = array();
            $args['meta_query'][] = $meta_query;
        }

        $args['meta_key'] = 'hki_event_start_time';
        $args['orderby'] = 'meta_value';
        $args['order'] = 'ASC';
        
        return $args;

    }

    /**
     * Use an external image as the post thumbnail
     * 
     */
    public function filter_event_thumbnail( $html, $post_id, $thumbnail_id ) {

        if ( ! $thumbnail_id ) {

            $src            = get_post_meta( $post_id, 'hki_event_image_url', true );
            $alt            = get_post_meta( $post_id, 'hki_event_image_alt_text', true );
            $fallback_img   = 'https://i.imgur.com/XBaFPUf.png';

            if ( preg_match( "/\.(jpg|jpeg|img|webp|png|svg|gif)/" , $src ) ) {

                $alt_str = !empty ( $alt ) ? 'alt="'.$alt.'"' : '';

                if ( $src ) {
                    $html = '<img src="' . $src . '" '.$alt_str.' loading="lazy" onerror="'.'this.src="'.$fallback_img.'"">';
                }

            }

        }

        return $html;

    }

    /**
     * Enqueue plugin stylesheet
     */
    public function enqueue_assets() {

        global $post;

        $version_number = 1 + rand(0, 1000) / 1000;

        if ( has_shortcode( $post->post_content, HE_SHORTCODE ) )  {
            wp_enqueue_script( 'hki_events_script', HE_URL . '/assets/script.js', array(), $version_number );
        }

        wp_enqueue_style( 'hki_events_style', HE_URL . '/assets/style.css', array(), $version_number, 'all' );

    }

    /**
     * A callback function for hki_events shortcode. Outputs a list of events.
     * 
     * @return string html
     */
    public function shortcode() {

        require_once ( HE_DIR . '/inc/template-functions.php' );

        $output = '<div id="hki-events-shortcode">';

        $query = he_get_event_query();
        $post_index = 0;
        $found_posts = $query->found_posts;

        if ( $query->have_posts() ):
            ob_start();
            require ( HE_DIR . '/template-parts/event-list-filters.php' );
            require ( HE_DIR . '/template-parts/event-list.php' );
            if ( $query->max_num_pages > 1 ):
                require ( HE_DIR . '/template-parts/event-list-loadmore.php' );
            endif;
        else:
            echo '<div class="no-posts">'.__( 'Ei tapahtumia', 'hki_events' ).'</div>';
        endif; 

        wp_reset_postdata();

        $output .= ob_get_clean().'</div>';

        return $output;

    }
    
    /**
     * Demo footer and SVGs
     */
    public function hki_events_footer() {

        echo '<footer class="hki-events-footer">
        <div class="footer-content">
        <div class="footer-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#0f13fa" fill-opacity="1" d="M0,192L30,213.3C60,235,120,277,180,272C240,267,300,213,360,192C420,171,480,181,540,208C600,235,660,277,720,293.3C780,309,840,299,900,277.3C960,256,1020,224,1080,192C1140,160,1200,128,1260,138.7C1320,149,1380,203,1410,229.3L1440,256L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path></svg>
        </div>
        <div class="footer-bg"></div>
        </div>
        <div class="footer-sun"></div>
        <div class="footer-cloud">
        <svg height="400" width="500" id="cloud">
        <circle cx="250" cy="160" r="100" fill="#ffffff"></circle>
        <circle cx="210" cy="240" r="80" fill="#ffffff"></circle>
        <circle cx="130" cy="200" r="80" fill="#ffffff"></circle>
        <circle cx="310" cy="250" r="60" fill="#ffffff"></circle>
        <circle cx="390" cy="230" r="70" fill="#ffffff"></circle>
        <circle cx="360" cy="130" r="50" fill="#ffffff"></circle>
        </svg>
        </div>
        </footer>';

    }
}