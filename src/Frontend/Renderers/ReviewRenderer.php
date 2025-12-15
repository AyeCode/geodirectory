<?php
/**
 * Review Renderer
 *
 * Handles rendering of individual review/comment items in WordPress comment lists.
 *
 * @package GeoDirectory\Frontend\Renderers
 * @since   3.0.0
 */

declare(strict_types=1);

namespace AyeCode\GeoDirectory\Frontend\Renderers;

use AyeCode\GeoDirectory\Core\Services\Tables;

/**
 * Review Renderer Class
 *
 * Provides callback functions for wp_list_comments() to render individual
 * review items using GeoDirectory Bootstrap templates.
 *
 * @since 3.0.0
 */
final class ReviewRenderer {
	/**
	 * Tables service instance.
	 *
	 * @var Tables
	 */
	private Tables $tables;

	/**
	 * Constructor.
	 *
	 * @param Tables $tables Tables service for database table names.
	 */
	public function __construct( Tables $tables ) {
		$this->tables = $tables;
	}

	/**
	 * WordPress comment list callback.
	 *
	 * Used by wp_list_comments() to render individual review/comment items.
	 * Supports pingbacks, trackbacks, and standard reviews.
	 *
	 * @param \WP_Comment $comment The comment object.
	 * @param array       $args    wp_list_comments() arguments.
	 * @param int         $depth   Comment depth level.
	 * @return void
	 */
	public function list_comments_callback( $comment, array $args, int $depth ): void {
		global $gd_review_template;

		$GLOBALS['comment'] = $comment;

		switch ( $comment->comment_type ) {
			case 'pingback':
			case 'trackback':
				// Display trackbacks differently than normal comments.
				?>
				<li <?php comment_class( 'geodir-comment' ); ?> id="comment-<?php comment_ID(); ?>">
					<p><?php _e( 'Pingback:', 'geodirectory' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'geodirectory' ), '<span class="edit-link">', '</span>' ); ?></p>
				<?php
				break;

			default:
				// Proceed with normal comments/reviews.
				// Bootstrap is the only design style in v3.
				if ( ! empty( $gd_review_template ) ) {
					$gd_review_template = esc_attr( sanitize_file_name( $gd_review_template ) );
					$template = 'bootstrap/reviews/item-' . $gd_review_template . '.php';
				} else {
					$template = 'bootstrap/reviews/item.php';
				}

				$vars = [
					'comment' => $comment,
					'args'    => $args,
					'depth'   => $depth,
					'rating'  => geodir_get_comment_rating( $comment->comment_ID ),
				];

				echo geodir_get_template_html( $template, $vars );

				break;
		}
	}

	/**
	 * Get the callback array for use with wp_list_comments().
	 *
	 * Returns an array suitable for passing to wp_list_comments() callback parameter.
	 *
	 * @return array Callback array [object, method_name].
	 */
	public function get_callback(): array {
		return [ $this, 'list_comments_callback' ];
	}

	/**
	 * Get overall rating box HTML for Bootstrap 5.
	 *
	 * Generates a complete rating overview with breakdown by star rating.
	 *
	 * @param int $post_id The post ID.
	 * @return string HTML output.
	 */
	public function get_overall_box_html( int $post_id ): string {
		$rating_titles       = $this->get_rating_texts();
		$post_rating         = geodir_get_post_rating( $post_id );
		$post_rating_rounded = round( $post_rating );
		$review_total        = geodir_get_review_count_total( $post_id );
		$stars               = geodir_get_rating_stars( $post_rating, $post_id );
		$stars               = str_replace( 'd-flex', '', $stars );
		$rating_counts       = $this->get_post_review_rating_counts( $post_id );
		$rating_count        = 5; // Always 5 stars

		ob_start();
		?>
		<div class="row gy-4 mb-5">
			<div class="col-sm-4">
				<div class="card border-0 rounded bg-transparent-primary bg-primary bg-opacity-10" >
					<div class="card-body text-center text-dark">
						<div class="mb-1">
							<?php echo ( isset( $rating_titles[ $post_rating_rounded ] ) ? $rating_titles[ $post_rating_rounded ] : '' ); ?>
						</div>
						<div class="mb-1 display-5">
							<?php echo round( $post_rating, 1 ); ?>
						</div>
						<div class="mb-1">
							<?php echo $stars; ?>
						</div><span class="fs-xs">
							<?php printf( _n( '%d review', '%d reviews', $review_total, 'geodirectory' ), number_format_i18n( $review_total ) ); ?>
						</span>
					</div>
				</div>
			</div>
			<div class="col-sm-8">
				<?php echo $this->get_post_rating_counts_html( $post_id ); ?>
			</div>
		</div>
		<?php
		return apply_filters( 'geodir_comments_overall_box_html', ob_get_clean(), $post_id );
	}

	/**
	 * Get rating texts for star ratings.
	 *
	 * @return array Array of rating texts keyed by rating number.
	 */
	private function get_rating_texts(): array {
		$defaults = [
			1 => __( 'Terrible', 'geodirectory' ),
			2 => __( 'Poor', 'geodirectory' ),
			3 => __( 'Average', 'geodirectory' ),
			4 => __( 'Very Good', 'geodirectory' ),
			5 => __( 'Excellent', 'geodirectory' ),
		];

		$texts = [
			1 => geodir_get_option( 'rating_text_1' ) ? __( geodir_get_option( 'rating_text_1' ), 'geodirectory' ) : $defaults[1],
			2 => geodir_get_option( 'rating_text_2' ) ? __( geodir_get_option( 'rating_text_2' ), 'geodirectory' ) : $defaults[2],
			3 => geodir_get_option( 'rating_text_3' ) ? __( geodir_get_option( 'rating_text_3' ), 'geodirectory' ) : $defaults[3],
			4 => geodir_get_option( 'rating_text_4' ) ? __( geodir_get_option( 'rating_text_4' ), 'geodirectory' ) : $defaults[4],
			5 => geodir_get_option( 'rating_text_5' ) ? __( geodir_get_option( 'rating_text_5' ), 'geodirectory' ) : $defaults[5],
		];

		return apply_filters( 'geodir_rating_texts', $texts );
	}

	/**
	 * Get rating counts breakdown HTML.
	 *
	 * @param int $post_id The post ID.
	 * @return string HTML output.
	 */
	private function get_post_rating_counts_html( int $post_id ): string {
		$args = [
			'rating_icon' => geodir_get_option( 'rating_icon', 'fas fa-star' ),
			'rating_icon_fw' => geodir_get_option( 'rating_icon_fw' ),
			'rating_color' => geodir_get_option( 'rating_color' ),
			'rating_color_off' => geodir_get_option( 'rating_color_off' )
		];

		if ( ! empty( $args['rating_icon'] ) ) {
			$args['rating_icon'] = geodir_sanitize_html_class( $args['rating_icon'] );
		}

		if ( empty( $args['rating_icon'] ) ) {
			$args['rating_icon'] = 'fas fa-star';
		}

		if ( ! empty( $args['rating_icon_fw'] ) ) {
			$args['rating_icon'] .= ' fa-fw';
		}

		if ( ! empty( $args['rating_color'] ) ) {
			$args['rating_color'] = sanitize_hex_color( $args['rating_color'] );
		}

		// Default color for Bootstrap 5.
		if ( empty( $args['rating_color'] ) || $args['rating_color'] === '#ff9900' ) {
			$args['rating_color'] = '#ffc107';
		}

		if ( ! empty( $args['rating_color_off'] ) ) {
			$args['rating_color_off'] = sanitize_hex_color( $args['rating_color_off'] );
		}

		// Default off color for Bootstrap 5.
		if ( empty( $args['rating_color_off'] ) || $args['rating_color_off'] === '#afafaf' ) {
			$args['rating_color_off'] = '#efecf3';
		}

		$args = apply_filters( 'geodir_post_rating_star_count_output_args', $args, $post_id );

		$review_total  = geodir_get_review_count_total( $post_id );
		$rating_counts = $this->get_post_review_rating_counts( $post_id );
		$rating_count  = 5; // Always 5 stars
		$row_class     = $rating_count > 5 ? 'row-cols-2' : 'row-cols-1';

		ob_start();
		?>
		<div class="row <?php echo esc_attr( $row_class ); ?> gy-3" data-total="<?php echo absint( $review_total ); ?>">
			<?php
			while ( $rating_count > 0 ) {
				$ratings = isset( $rating_counts[ $rating_count ] ) ? absint( $rating_counts[ $rating_count ] ) : 0;
				$percent = $ratings && $review_total ? round( ( $ratings / $review_total ) * 100 ) : 0;
				?>
				<div class="col">
					<div class="d-flex align-items-center">
						<div class="pe-2 text-nowrap text-center fs-sm" style="min-width:50px"><?php echo absint( $rating_count ); ?> <i class="<?php echo esc_attr( $args['rating_icon'] ); ?> text-gray" style="color:<?php echo esc_attr( $args['rating_color'] ); ?>!important" aria-hidden="true"></i></div>
						<div class="progress w-100" style="height:14px;background-color:<?php echo esc_attr( $args['rating_color_off'] ); ?>!important" data-count="<?php echo absint( $ratings ); ?>">
							<div class="progress-bar" role="progressbar" style="width:<?php echo absint( $percent ); ?>%;background-color:<?php echo esc_attr( $args['rating_color'] ); ?>!important" aria-valuenow="<?php echo absint( $percent ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
						<div class="ps-2 text-nowrap text-end fs-sm" style="min-width:40px"><?php echo absint( $ratings ); ?></div>
					</div>
				</div>
				<?php
				$rating_count--;
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get post review rating counts.
	 *
	 * Returns an array with rating counts for each star level.
	 *
	 * @param int  $post_id     The post ID.
	 * @param bool $force_query Force fresh query instead of cache.
	 * @return array Rating counts keyed by rating number.
	 */
	private function get_post_review_rating_counts( int $post_id, bool $force_query = false ): array {
		global $wpdb;

		// Check for cache.
		$cache = wp_cache_get( 'gd_post_review_rating_counts_' . $post_id, 'gd_post_review_rating_counts' );

		if ( $cache !== false && ! $force_query ) {
			/**
			 * Filter post review rating counts cached results.
			 *
			 * @since 2.3.76
			 *
			 * @param array $cache       Cached review rating counts array.
			 * @param int   $post_id     Current post ID.
			 * @param bool  $force_query Force query to skip cached results.
			 */
			return apply_filters( 'geodir_post_review_rating_counts', $cache, $post_id, $force_query );
		}

		$sql = $wpdb->prepare(
			"SELECT `r`.`rating` FROM `" . $this->tables->get( 'reviews' ) . "` AS `r`
			JOIN `{$wpdb->comments}` AS `cmt` ON `cmt`.`comment_ID` = `r`.`comment_id`
			WHERE `r`.`post_id` = %d AND `cmt`.`comment_approved` = '1' AND `r`.`rating` > 0",
			$post_id
		);

		/**
		 * Filter post review rating counts SQL query.
		 *
		 * @since 2.3.76
		 *
		 * @param string $sql         SQL Query.
		 * @param int    $post_id     Current post ID.
		 * @param bool   $force_query Force query to skip cached results.
		 */
		$sql = apply_filters( 'geodir_post_review_rating_counts_sql', $sql, $post_id, $force_query );

		$results = $wpdb->get_results( $sql );

		$counts = [];

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				isset( $counts[ $result->rating ] ) ? $counts[ $result->rating ]++ : $counts[ $result->rating ] = 1;
			}
		}

		// Cache the results.
		wp_cache_set( 'gd_post_review_rating_counts_' . $post_id, $counts, 'gd_post_review_rating_counts', 3600 );

		/**
		 * Filter post review rating counts.
		 *
		 * @since 2.3.76
		 *
		 * @param array $counts      Review rating counts array.
		 * @param int   $post_id     Current post ID.
		 * @param bool  $force_query Force query to skip cached results.
		 */
		return apply_filters( 'geodir_post_review_rating_counts', $counts, $post_id, $force_query );
	}
}
