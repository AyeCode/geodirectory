<?php geodir_listing_loop_start(); ?>

    <?php while ( have_posts() ) { ?>
        <?php the_post(); ?>

        <?php do_action( 'geodir_listing_loop_content' ); ?>

        <?php geodir_get_template_part( 'listing/content', '' ); ?>

    <?php } // end of the loop. ?>

<?php geodir_listing_loop_end(); ?>