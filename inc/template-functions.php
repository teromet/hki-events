<?php

/**
 * Functions which are needed by the shortcode.
 * 
 * It makes sense to keep them separate from the plugin source code dir
 * because usually they fall under the theme being used.
 *
 */

/**
 * Get event meta query args
 * 
 * @return array $meta_query
 * 
 */
function he_get_event_meta_query() {

    $today = date( 'Ymd' );

    $meta_query = array(
        array(
            'key'     	=> 'hki_event_end_time',
            'compare' 	=> '>=',
            'value'   	=> $today,
            'type' 		=> 'DATE'
        )
    );

    return $meta_query;

}

/**
 * Get WP_Query for events
 * 
 * @return WP_Query
 */
function he_get_event_query() {

    $meta_query = he_get_event_meta_query();

    $args = array(
        'post_type'         =>  HE_POST_TYPE,
        'meta_key'          => 'hki_event_start_time',
        'orderby'           => 'meta_value',
        'order'             => 'ASC',
        'posts_per_page' 	=>  9,
        'meta_query'		=> $meta_query
    );     
    
    return new \WP_Query( $args );

}

/**
 * Output tag filters HTML
 * 
 * @return string html
 */
function he_tag_filters( $found_posts ) {

    $tag_counts  = he_get_tag_counts();
    $filters = '<span class="hki-events-list-filters-item show-all selected">'.__('Näytä kaikki', 'hki_events' ).' ('.$found_posts.')</span>';

    if ( ! empty( $tag_counts ) ) {
        foreach ( $tag_counts as $slug => $term ) {
            if( $term['count'] >= 2 ) {
                $filters .= '<span class="hki-events-list-filters-item" data-term-slug="'.$slug.'" data-term-id="'.$term['term_id'].'" >'.$term['name'].' ('.$term['count'].')</span>';
            }
        }
    }

    echo $filters;

}

/**
 * Count upcoming events of every tag and sort the results
 * 
 * @return array $tag_counts
 */
function he_get_tag_counts() {

    $meta_query = he_get_event_meta_query();
    $tag_counts = array();

    $event_ids = get_posts( array(
        'post_type'       => HE_POST_TYPE,
        'fields'          => 'ids', // Only get post IDs
        'posts_per_page'  => -1,
        'meta_query'	  => $meta_query
    ) );

    if ( ! empty ( $event_ids ) ) {
        foreach ( $event_ids as $event_id ) {

            $object_terms = wp_get_object_terms( $event_id, HE_TAXONOMY );

            if ( ! empty ( $object_terms ) && ! is_wp_error( $object_terms )  ) {

                foreach ( $object_terms as $term ) {

                    if ( ! array_key_exists( $term->slug, $tag_counts ) ) {
                        $tag_counts[$term->slug]['name'] = $term->name;
                        $tag_counts[$term->slug]['term_id'] = $term->term_id;
                        $tag_counts[$term->slug]['count'] = 1;
                    }
                    else {
                        $tag_counts[$term->slug]['count'] = $tag_counts[$term->slug]['count'] + 1;
                    }

                }

            }
        }
    }

    if ( ! empty ( $tag_counts ) ) {

        usort( $tag_counts, function( $a, $b ) {
            if ( $a['count'] < $b['count'] ) {
                return 1;
            } elseif ( $a['count'] > $b['count'] ) {
                return -1;
            }
            elseif ( $a['count'] == $b['count'] ) {
                if ( strcmp( $a['name'], $b['name'] ) == 1 ) {
                    return 1;
                } elseif ( strcmp( $a['name'], $b['name'] ) == -1 ) {
                    return -1;
                }
            }
            return 0;
        });

    }

    return $tag_counts;

}