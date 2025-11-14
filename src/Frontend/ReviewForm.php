<?php
/**
 * Review Form Manager
 *
 * Handles all modifications and rendering for the comment/review form.
 *
 * @package GeoDirectory\Frontend
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Frontend;

use AyeCode\GeoDirectory\Core\Services\Settings;

final class ReviewForm {
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings utility.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Modifies the WordPress comment form defaults to style it and add/change fields.
	 *
	 * @param array $defaults The default WordPress comment form arguments.
	 * @return array The modified arguments.
	 */
	public function modify_form_defaults( array $defaults ): array {
		// @todo Refactor the global aui() helper into an injectable service.
		if ( ! function_exists( 'aui' ) || ! geodir_is_page( 'single' ) ) {
			return $defaults;
		}

		$commenter = wp_get_current_commenter();

		$defaults['comment_field'] = aui()->textarea( [
			'name'        => 'comment',
			'id'          => 'comment',
			'placeholder' => esc_html__( 'Enter your review comments here (required)...', 'geodirectory' ),
			'required'    => true,
			'label'       => esc_html__( 'Review text', 'geodirectory' ),
			'rows'        => 8,
		] );

		$defaults['fields']['author'] = aui()->input( [
			'name'     => 'author',
			'required' => true,
			'label'    => esc_html__( 'Name', 'geodirectory' ),
			'value'    => $commenter['comment_author'] ?? '',
		] );

		$defaults['fields']['email'] = aui()->input( [
			'name'     => 'email',
			'required' => true,
			'label'    => esc_html__( 'Email', 'geodirectory' ),
			'type'     => 'email',
			'value'    => $commenter['comment_author_email'] ?? '',
		] );

		$defaults['fields']['url'] = aui()->input( [
			'name'  => 'url',
			'label' => esc_html__( 'Website', 'geodirectory' ),
			'type'  => 'url',
			'value' => $commenter['comment_author_url'] ?? '',
		] );

		$defaults['logged_in_as']         = str_replace( 'logged-in-as', 'logged-in-as mb-3', $defaults['logged_in_as'] );
		$defaults['comment_notes_before'] = aui()->alert( [
			'type'    => 'info',
			'content' => __( 'Your email address will not be published.', 'geodirectory' ),
		] );

		$defaults['class_submit'] = 'btn btn-primary';
		$defaults['label_submit'] = esc_html__( 'Post Review', 'geodirectory' );
		$defaults['title_reply']  = '<span class="gd-comment-review-title h4">Leave a Review</span>';

		return $defaults;
	}

	/**
	 * Renders the star rating input field.
	 *
	 * This is hooked into the comment form.
	 *
	 * @return void
	 */
	public function render_rating_input(): void {
		global $post;

		if ( ! $post || geodir_cpt_has_rating_disabled( $post->post_type ) ) {
			return;
		}

		// @todo Refactor the global aui() helper into an injectable service.
		$aui_bs5 = function_exists( 'aui' ) ? aui()->is_bs5() : false;

		echo '<div class="' . ( $aui_bs5 ? 'mb-3' : 'form-group' ) . ' form-control h-auto rounded px-3 pt-3 pb-3 gd-rating-input-group">';
		echo $this->render_rating_html( 0, 'input' );
		echo '</div>';
	}

	/**
	 * Generates the HTML for the star rating display (either for input or output).
	 *
	 * @param float  $rating    The rating value (e.g., 4.5).
	 * @param string $type      The type of display: 'input' or 'output'.
	 * @param array  $overrides Optional overrides for settings.
	 *
	 * @return string The generated HTML.
	 */
	public function render_rating_html( float $rating, string $type = 'output', array $overrides = [] ): string {
		$args = wp_parse_args( $overrides, [
			'rating_icon'        => (string) $this->settings->get( 'rating_icon', 'fas fa-star' ),
			'rating_color'       => (string) $this->settings->get( 'rating_color', '#ffb200' ),
			'rating_color_off'   => (string) $this->settings->get( 'rating_color_off', '#ccc' ),
			'rating_texts'       => $this->get_rating_texts(),
			'rating_input_count' => 5,
			'id'                 => 'geodir_overallrating',
		] );

		$rating_percent = ( $type === 'output' && $rating > 0 ) ? 'width:' . ( $rating / $args['rating_input_count'] * 100 ) . '%;' : '';

		$foreground_style = "style='{$rating_percent} color:{$args['rating_color']};'";
		$background_style = "style='color:{$args['rating_color_off']};'";

		$rating_wrap_title = '';
		if ( $type === 'output' ) {
			$rating_wrap_title = $rating > 0 ? sprintf( esc_attr__( '%s star rating', 'geodirectory' ), $rating ) : esc_attr__( 'No rating yet!', 'geodirectory' );
		}

		$stars_html = '';
		for ( $i = 1; $i <= $args['rating_input_count']; $i++ ) {
			$star_title = $type === 'input' ? "title='{$args['rating_texts'][$i]}'" : '';
			$stars_html .= "<i class='{$args['rating_icon']}' aria-hidden='true' {$star_title}></i>";
		}

		ob_start();
		?>
		<div class="gd-rating-outer-wrap gd-rating-<?php echo esc_attr( $type ); ?>-wrap">
			<div class="gd-rating gd-rating-<?php echo esc_attr( $type ); ?>">
				<span class="gd-rating-wrap" <?php if ($rating_wrap_title) { echo 'title="' . $rating_wrap_title . '"'; } ?>>
					<span class="gd-rating-foreground" <?php echo $foreground_style; ?>><?php echo $stars_html; ?></span>
					<span class="gd-rating-background" <?php echo $background_style; ?>><?php echo $stars_html; ?></span>
				</span>
				<?php if ( $type === 'input' ) : ?>
					<span class="gd-rating-text" data-title="<?php esc_attr_e( 'Select a rating', 'geodirectory' ); ?>"><?php esc_html_e( 'Select a rating', 'geodirectory' ); ?></span>
					<input type="hidden" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $rating ); ?>"/>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Gets the configured rating texts.
	 *
	 * @return array The rating texts for stars 1 through 5.
	 */
	private function get_rating_texts(): array {
		$defaults = [
			1 => __( 'Terrible', 'geodirectory' ),
			2 => __( 'Poor', 'geodirectory' ),
			3 => __( 'Average', 'geodirectory' ),
			4 => __( 'Very Good', 'geodirectory' ),
			5 => __( 'Excellent', 'geodirectory' ),
		];

		$texts = [];
		for ($i = 1; $i <= 5; $i++) {
			$texts[$i] = $this->settings->get( "rating_text_{$i}" ) ?: $defaults[$i];
		}

		return apply_filters( 'geodir_rating_texts', $texts );
	}

	/**
	 * Gets the default, untranslated rating texts.
	 *
	 * This is a static helper for use in config files or other places
	 * where an object instance is not available.
	 *
	 * @return array The default rating texts.
	 */
	public static function get_default_rating_texts(): array {
		return [
			1 => 'Terrible',
			2 => 'Poor',
			3 => 'Average',
			4 => 'Very Good',
			5 => 'Excellent',
		];
	}

}
