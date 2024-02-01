<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\HE_CPT as CPT;
use HkiEvents\HE_Event_Creator as Event_Creator;
use HkiEvents\HE_Settings_Page as Settings_Page;
use HkiEvents\HE_Utils as Utils;

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
        // Create custom taxonomy
        $this->create_taxonomies();

        // Add cron schedule
        add_action( self::CRON_HOOK, array( $this, 'handle_cron' ) );
        $this->schedule_cron();

        // Add assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Add shortcode
        add_shortcode( 'hki_events', array( $this, 'shortcode' ) );

        // Add menu page
        $settings_page = new Settings_Page();

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
            'menu_icon' => 'dashicons-groups',
            'taxonomies' => array( HE_TAXONOMY )
        );

        $cpt = new CPT( $cpt_args );
        $cpt->register();

    }

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
            'show_admin_column' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array( 'slug' => 'tag' )
        ) );
    
    }

    private function schedule_cron() {

        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
          wp_schedule_event( time(), 'daily', self::CRON_HOOK );
        }

    }

    public function handle_cron() {

        Utils::log('cron-log', 'Cron executed');
        $event_creator = new Event_Creator();
        $event_creator->get_events();

    }

    public function filter_event_thumbnail( $html, $post_id, $thumbnail_id ) {

        if ( ! $thumbnail_id ) {

            $src = get_post_meta( $post_id, 'hki_event_image_url', true );
            $alt = get_post_meta( $post_id, 'hki_event_image_alt_text', true );

            $alt_str = !empty ( $alt ) ? 'alt="'.$alt.'"' : '';

            if ( $src ) {
                $html = '<img src="' . $src . '" '.$alt_str.' loading="lazy">';
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

        if ( $query->have_posts() ):
            ob_start();
            require ( HE_DIR . '/template-parts/event-list-filters.php' );
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
            'post_type'         =>  HE_POST_TYPE,
            'meta_key'          => 'hki_event_start_time',
            'orderby'           => 'meta_value',
            'order'             => 'ASC',
            'posts_per_page' 	=>  -1,
            'meta_query'		=> $meta_query
        );     
        
        return new \WP_Query( $args );

    }

    /**
     * Get all post_tag terms
     * 
     * @return WP_Term[] or false on error
     */
    private function get_tag_terms() {

        $event_ids = get_posts( array(
            'post_type'       => HE_POST_TYPE,
            'fields'          => 'ids', // Only get post IDs
            'posts_per_page'  => -1
        ) );

        $terms = wp_get_object_terms( $event_ids, HE_TAXONOMY );

        return ! is_wp_error( $terms ) ? $terms : false;

    }

    /**
     * Output tag filters HTML
     * 
     * @return string html
     */
    public function event_tag_filters() {

        $tag_terms  = $this->get_tag_terms();
        $filters = '';

        if ( ! empty( $tag_terms ) ) {
            foreach ( $tag_terms as $term ) {
                $filters .= '<span class="hki-events-list-filters-item">'.$term->name.' ('.$term->count.')</span>';
            }
        }

        echo $filters;

    }
}