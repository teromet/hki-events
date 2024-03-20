<div class="hki-events-list">

<?php while ( $query->have_posts() ) : $query->the_post();

$post_id            = get_the_ID();
$image              = get_post_thumbnail_id( $post_id );
$image_size         = 'full'; // (thumbnail, medium, large, full or custom size)
$start_date         = get_post_meta( $post_id, 'hki_event_start_time', true );
$end_date           = get_post_meta( $post_id, 'hki_event_end_time', true );  
$start              = date( 'j.n.Y', strtotime( $start_date ) );
$end                = date( 'j.n.Y', strtotime( $end_date ) );
$terms              = get_the_terms( $post_id, HE_TAXONOMY );
$list_item_class    = 'hki-events-list-item';

if ( ! empty ( $terms ) ) {
    $terms_slugs = array_map(
        function( $term ) { return $term->slug; },
        array_values( $terms )
    );
    $list_item_class = $list_item_class.' '.implode( ' ', $terms_slugs );
}


require ( HE_DIR . '/template-parts/event-list-item.php' );

endwhile; ?>

</div>