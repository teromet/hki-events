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

if( strtotime( $start_date ) < strtotime( $end_date ) ) {
    $start = $start.' - '.$end;
}

require ( HE_DIR . '/template-parts/event-list-item.php' );

$post_index++;

endwhile; ?>

</div>