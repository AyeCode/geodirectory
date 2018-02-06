<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class GeoDir_Comments {

	/**
	 * Initiate the comments class.
	 */
	public static function init() {
		add_action( 'comment_form_logged_in_after', array( __CLASS__, 'rating_input' ) );
		add_action( 'comment_form_before_fields', array( __CLASS__, 'rating_input' ) );

		// add ratings to comment text
		add_filter( 'comment_text', array(__CLASS__, 'wrap_comment_text'), 40, 2 );

	}

	/**
	 * Add rating information in comment text.
	 *
	 * @param string $content The comment content.
	 * @param object|string $comment The comment object.
	 *
	 * @return string The comment content.
	 */
	function wrap_comment_text( $content, $comment = '' ) {
		if ( ! empty( $comment->comment_post_ID ) && geodir_cpt_has_rating_disabled( (int) $comment->comment_post_ID ) ) {
			if ( ! is_admin() ) {
				return '<div class="description">' . $content . '</div>';
			} else {
				return $content;
			}
		} else {
			$rating = 0;
			if ( ! empty( $comment ) ) {
				$rating = geodir_get_commentoverall( $comment->comment_ID );
			}
			if ( $rating != 0 && ! is_admin() ) {
				return '<div><div class="gd-rating-text">' . __( 'Overall Rating', 'geodirectory' ) . ': <div class="rating">' . $rating . '</div></div>' . geodir_get_rating_stars( $rating, $comment->comment_ID ) . '</div><div class="description">' . $content . '</div>';
			} else {
				return $content;
			}
		}
	}

	/**
	 * Add rating fields in comment form.
	 *
	 * Adds a rating input field in comment form.
	 *
	 * @since 1.0.0
	 * @since 1.6.16 Changes for disable review stars for certain post type.
	 * @package GeoDirectory
	 * @global object $post The post object.
	 */
	function rating_input( $comment = array() ) {
		global $post;


		if ( isset( $comment->comment_post_ID ) && $comment->comment_post_ID ) {
			$post_type = get_post_type( $comment->comment_post_ID );
		} else {
			$post_type = $post->post_type;
		}
		$post_types = geodir_get_posttypes();

		if ( ! empty( $post_type )
		     && in_array( $post->post_type, $post_types )
		     && ! ( ! empty( $post->post_type ) && geodir_cpt_has_rating_disabled( $post_type ) )
		) {
			$rating = 0;
			if ( isset( $comment->comment_post_ID ) && $comment->comment_post_ID ) {
				$rating = geodir_get_commentoverall( $comment->comment_ID );
			}
			echo self::rating_input_html( $rating );
		}
	}
	
	/**
	 * The rating input html.
	 */
	public static function rating_input_html( $rating ) {
		return self::rating_html( $rating, 'input' );
	}

	/**
	 * Get the default rating count.
	 *
	 * @return int
	 */
	public static function rating_input_count() {
		return 5;
	}

	/**
	 * Get the rating input html.
	 *
	 * @param $rating
	 * @param string $type
	 *
	 * @return string
	 */
	public static function rating_html( $rating, $type = 'output' ) {

		$rating_icon  = esc_attr( geodir_get_option( 'rating_icon', 'fa-star' ) );
		$rating_color = esc_attr( geodir_get_option( 'rating_color' ) );
		if ( $rating_color == '#ff9900' ) {
			$rating_color = '';
		}
		$rating_color_off = esc_attr( geodir_get_option( 'rating_color_off' ) );
		if ( $rating_color_off == '#afafaf' ) {
			$rating_color_off = '';
		} else {
			$rating_color_off = "style='color:$rating_color_off;'";
		}
		$rating_texts      = self::rating_texts();
		$rating_wrap_title = '';
		if ( $type == 'output' ) {
			$rating_wrap_title = $rating ? sprintf( __( '%d star rating', 'geodirectory' ), $rating ) : __( "No rating yet!", "geodirectory" );
		}
		$rating_html        = '';
		$rating_input_count = self::rating_input_count();
		$i                  = 1;
		$rating_type        = esc_attr( geodir_get_option( 'rating_type' ) );
		if ( $rating_type == 'image' && $rating_image_id = geodir_get_option( 'rating_image' ) ) {
			$rating_image = wp_get_attachment_url( $rating_image_id );
			while ( $i <= $rating_input_count ) {
				$rating_title = $type == 'input' ? "title='$rating_texts[$i]'" : '';
				$rating_html .= '<img alt="rating icon" src="' . $rating_image . '" ' . $rating_title . ' />';
				$i ++;
			}
			if ( $rating_color == '#ff9900' ) {
				$rating_color = '';
			} else {
				$rating_color = "background:$rating_color;";
			}
		} else {
			while ( $i <= $rating_input_count ) {
				$rating_title = $type == 'input' ? "title='$rating_texts[$i]'" : '';
				$rating_html .= '<i class="fa ' . $rating_icon . '" ' . $rating_title . '></i>';
				$i ++;
			}
		}

		$rating_percent   = $type == 'output' ? 'width:' . $rating / $rating_input_count * 100 . '%;' : '';
		$foreground_style = $rating_percent || $rating_color ? "style='$rating_percent $rating_color'" : '';

		ob_start();
		?>
		<div class="gd-rating gd-rating-<?php echo esc_attr( $type ); ?> gd-rating-type-<?php echo $rating_type; ?>">
			<span class="gd-rating-wrap" <?php if ( $rating_wrap_title ) {
				echo "title='" . esc_attr( $rating_wrap_title ) . "''";
			} ?>>
				<span class="gd-rating-foreground" <?php echo $foreground_style; ?>>
				<?php echo $rating_html; ?>
				</span>
				<span class="gd-rating-background" <?php echo $rating_color_off; ?>>
				<?php echo $rating_html; ?>
				</span>
			</span>
			<?php if ( $type == 'input' ) { ?>
				<span class="gd-rating-text"
				      data-title="<?php _e( 'Select a rating', 'geodirectory' ); ?>"><?php _e( 'Select a rating', 'geodirectory' ); ?></span>
				<input type="hidden" id="geodir_overallrating" name="geodir_overallrating"
				       value="<?php echo esc_attr( $rating ); ?>"/>
			<?php } ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get the rating output html.
	 *
	 * @param $rating
	 *
	 * @return string
	 */
	public static function rating_output( $rating ) {
		return self::rating_html( $rating );
	}

	/**
	 * The default rating texts.
	 *
	 * @return mixed|void
	 */
	public static function rating_texts_default() {
		$texts = array(
			1 => __( 'Terrible', 'geodirectory' ),
			2 => __( 'Poor', 'geodirectory' ),
			3 => __( 'Average', 'geodirectory' ),
			4 => __( 'Very Good', 'geodirectory' ),
			5 => __( 'Excellent', 'geodirectory' ),
		);

		return apply_filters( 'geodir_rating_texts_default', $texts );
	}

	/**
	 * The rating texts used on the site.
	 *
	 * @return mixed|void
	 */
	public static function rating_texts() {
		$defaults = self::rating_texts_default();

		$texts = array(
			1 => geodir_get_option( 'rating_text_1' ) ? __( geodir_get_option( 'rating_text_1' ), 'geodirectory' ) : $defaults[1],
			2 => geodir_get_option( 'rating_text_2' ) ? __( geodir_get_option( 'rating_text_2' ), 'geodirectory' ) : $defaults[2],
			3 => geodir_get_option( 'rating_text_3' ) ? __( geodir_get_option( 'rating_text_3' ), 'geodirectory' ) : $defaults[3],
			4 => geodir_get_option( 'rating_text_4' ) ? __( geodir_get_option( 'rating_text_4' ), 'geodirectory' ) : $defaults[4],
			5 => geodir_get_option( 'rating_text_5' ) ? __( geodir_get_option( 'rating_text_5' ), 'geodirectory' ) : $defaults[5],
		);
		
		return apply_filters( 'geodir_rating_texts', $texts );
	}

}