<div class="hki-events-list">

<?php while ( $query->have_posts() ) : $query->the_post();

$post_id    = get_the_ID();
$image      = get_post_thumbnail_id( $post_id );
$image_size = 'full'; // (thumbnail, medium, large, full or custom size)
$start_date = get_post_meta( $post_id, 'hki_event_start_time', true );
$end_date   = get_post_meta( $post_id, 'hki_event_end_time', true );  
$start      = date( 'j.n.Y k\l\o G.i', strtotime( $start_date ) );
$end        = date( 'j.n.Y k\l\o G.i', strtotime( $end_date ) );

require ( HE_DIR . '/template-parts/event-list-item.php' );

endwhile; ?>

</div>