<?php
/**
 * This file contains functions for outputting the listing/archive content.
 * @depreciated moved to page template
 */


// wrappers
add_action( 'geodir_listing_item_content_left', 'geodir_listing_item_content_left_wrapper_open', 0 );
add_action( 'geodir_listing_item_content_left', 'geodir_listing_item_content_wrapper_close', 50000 );
add_action( 'geodir_listing_item_content_right', 'geodir_listing_item_content_right_wrapper_open', 0 );
add_action( 'geodir_listing_item_content_right', 'geodir_listing_item_content_wrapper_close', 50000 );

// image
add_action( 'geodir_listing_item_content_left', 'geodir_listing_item_image', 10 );
// title
add_action( 'geodir_listing_item_content_right', 'geodir_listing_item_title', 15 );
// default actions
add_action( 'geodir_listing_item_content_right', 'geodir_listing_item_info', 20 );
// post meta, custom field listing location
add_action( 'geodir_listing_item_content_right', 'geodir_listing_post_meta', 25 );


/**
 * Add the custom field listing location info.
 */
function geodir_listing_post_meta() {
	echo geodir_show_listing_info( 'listing' );
}

/**
 * Add the some standard info like reviews and favourite.
 */
function geodir_listing_item_info() {
	global $gd_post;
	?>
	<div class="geodir-post-info">
        <span class="gd-list-rating-stars">
           <?php
           if ( ! empty( $gd_post->post_type ) && geodir_cpt_has_rating_disabled( $gd_post->post_type ) ) {
	           echo '<i class="fa fa-comments"></i>';
           } else {
	           $post_rating = geodir_get_post_rating( $gd_post->ID );
	           echo geodir_get_rating_stars( $post_rating, $gd_post->ID );
           }
           ?>
        </span>
        <span class="gd-list-rating-text">
            <a href="<?php comments_link(); ?>" class="gd-list-rating-link">
                <?php geodir_comments_number( $gd_post ); ?>
            </a>
        </span>
        <span class="gd-list-favorite">
            <?php geodir_favourite_html( '', $gd_post->ID ); ?>
        </span>

	</div>
	<?php
}

/**
 * Get the image for the listing items.
 * @todo make this ajax load images like a slider, pure css preferred.
 */
function geodir_listing_item_image() {
	global $gd_post, $post;
	$featured_image = geodir_show_featured_image( $post->ID, 'list-thumb', true, false, $gd_post->featured_image )
	?>
	<div class="geodir-post-img">
		<a href="<?php the_permalink(); ?>">
			<?php echo $featured_image; ?>
		</a>
	</div>
	<?php
}

/**
 * Get the title for the listing items.
 */
function geodir_listing_item_title() {
	?>
	<h2 class="geodir-entry-title">
		<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
	</h2>
	<?php
}

/**
 * Open the left listing item wrapper.
 */
function geodir_listing_item_content_left_wrapper_open() {
	echo "<div class='gd-list-item-left'>";
}

/**
 * Open the right listing item wrapper.
 */
function geodir_listing_item_content_right_wrapper_open() {
	echo "<div class='gd-list-item-right'>";
}

/**
 * Close the listing item wrappers.
 */
function geodir_listing_item_content_wrapper_close() {
	echo "</div>";
}