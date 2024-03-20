<?php

/**
 * Functions which are needed by the shortcode.
 * 
 * It makes sense to keep them separate from the plugin source code dir
 * because usually they fall under the theme being used.
 *
 */

/**
 * Get WP_Query for events
 * 
 * @return WP_Query
 */
function he_get_event_query() {

    $today = date( 'Ymd' );

    $meta_query = array(
            array(
                'key'     	=> 'hki_event_end_time',
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
function he_get_tag_terms() {

    $event_ids = get_posts( array(
        'post_type'       => HE_POST_TYPE,
        'fields'          => 'ids', // Only get post IDs
        'posts_per_page'  => -1
    ) );

    $terms = wp_get_object_terms( $event_ids, HE_TAXONOMY, array( 'orderby' => 'count', 'order' => 'DESC' ) );

    return ! is_wp_error( $terms ) ? $terms : false;

}

/**
 * Output tag filters HTML
 * 
 * @return string html
 */
function he_tag_filters() {

    $tag_terms  = he_get_tag_terms();
    $filters = '<span class="hki-events-list-filters-item show-all selected">'.__('Näytä kaikki', 'hki_events' ).'</span>';

    if ( ! empty( $tag_terms ) ) {
        foreach ( $tag_terms as $term ) {
            if( $term->count >= 2 ) {
                $filters .= '<span class="hki-events-list-filters-item" data-term-slug="'.$term->slug.'" >'.$term->name.' ('.$term->count.')</span>';
            }
        }
    }

    echo $filters;

}