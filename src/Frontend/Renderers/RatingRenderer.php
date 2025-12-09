<?php
/**
 * Rating Renderer
 *
 * Handles all star rating visual rendering for GeoDirectory reviews.
 *
 * @package GeoDirectory\Frontend\Renderers
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Frontend\Renderers;

use AyeCode\GeoDirectory\Core\Services\Settings;

/**
 * Renders star ratings for display and input.
 *
 * @since 3.0.0
 */
final class RatingRenderer {

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings service.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Renders the star rating HTML for display or input.
	 *
	 * @param float  $rating    The rating value (e.g., 4.5).
	 * @param string $type      The type of display: 'input' or 'output'.
	 * @param array  $overrides Optional overrides for settings.
	 * @return string The generated HTML.
	 */
	public function render_stars( float $rating, string $type = 'output', array $overrides = [] ): string {
		$args = wp_parse_args( $overrides, [
			'rating_icon'        => (string) $this->settings->get( 'rating_icon', 'fas fa-star' ),
			'rating_color'       => (string) $this->settings->get( 'rating_color', '#ffb200' ),
			'rating_color_off'   => (string) $this->settings->get( 'rating_color_off', '#ccc' ),
			'rating_texts'       => $this->get_rating_texts(),
			'rating_input_count' => 5,
			'id'                 => 'geodir_overallrating',
			'rating_label'       => $overrides['rating_label'] ?? '',
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
			<?php if ( ! empty( $args['rating_label'] ) ) : ?>
				<span class="gd-rating-label"><?php echo esc_html( $args['rating_label'] ); ?>: </span>
			<?php endif; ?>
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
	 * Renders the star rating input field for comment forms.
	 *
	 * @param float $rating The initial rating value.
	 * @return string The generated HTML.
	 */
	public function render_input( float $rating = 0 ): string {
		return $this->render_stars( $rating, 'input' );
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
}
