<div class="<?php echo $list_item_class;?>">
<div class="post-image">
    <?php the_post_thumbnail(); ?>
</div>
<div class="post-content">
    <div class="post-content-wrapper">
        <div class="post-date"><?php echo $start; ?></div>
        <div class="post-title"><?php the_title(); ?></div>
        <div class="post-button">
            <a class="btn btn-news btn-primary" href="<?php the_permalink(); ?>">
                <?php echo __('Lue lisää', 'hki_events' ); ?>
            </a>
        </div>
    </div>
    <div class="post-overlay"></div>
</div>
</div>