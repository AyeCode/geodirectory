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
			'rating_icon'        => esc_attr( (string) $this->settings->get( 'rating_icon', 'fas fa-star' ) ),
			'rating_icon_fw'     => esc_attr( (string) $this->settings->get( 'rating_icon_fw', '' ) ),
			'rating_color'       => esc_attr( (string) $this->settings->get( 'rating_color', '#ffb200' ) ),
			'rating_color_off'   => esc_attr( (string) $this->settings->get( 'rating_color_off', '#ccc' ) ),
			'rating_texts'       => $this->get_rating_texts(),
			'rating_image'       => $this->settings->get( 'rating_image' ),
			'rating_type'        => esc_attr( (string) $this->settings->get( 'rating_type', 'font-icon' ) ),
			'rating_input_count' => 5,
			'id'                 => 'geodir_overallrating',
			'rating_label'       => $overrides['rating_label'] ?? '',
		] );

		// Handle rating label filter for input type
		$rating_label = $args['rating_label'];
		if ( ! $rating_label && $type === 'input' ) {
			/**
			 * Filter the label for main rating.
			 *
			 * This is not shown everywhere but is used by reviews manager.
			 */
			$rating_label = apply_filters( 'geodir_overall_rating_label', '' );
		}

		// Build rating icon with optional fa-fw
		$rating_icon = $args['rating_icon'];
		if ( $args['rating_icon_fw'] ) {
			$rating_icon .= ' fa-fw';
		}

		// Prepare rating HTML (stars or images)
		$rating_html = '';
		$rating_type = $args['rating_type'];
		$rating_input_count = $args['rating_input_count'];

		if ( $rating_type === 'image' && ! empty( $args['rating_image'] ) ) {
			$rating_image = wp_get_attachment_url( $args['rating_image'] );
			for ( $i = 1; $i <= $rating_input_count; $i++ ) {
				$rating_title = $type === 'input' ? "title='{$args['rating_texts'][$i]}'" : '';
				$rating_html .= '<img alt="rating icon" src="' . esc_url( $rating_image ) . '" ' . $rating_title . ' />';
			}
			// For images, color is used as background
			$rating_color = $args['rating_color'] ? "background:{$args['rating_color']};" : '';
		} else {
			// Font icon mode
			for ( $i = 1; $i <= $rating_input_count; $i++ ) {
				$rating_title = $type === 'input' ? "title='{$args['rating_texts'][$i]}'" : '';
				$rating_html .= "<i class='{$rating_icon}' aria-hidden='true' {$rating_title}></i>";
			}
			$rating_color = $args['rating_color'] ? " color:{$args['rating_color']}; " : '';
		}

		// Calculate rating percentage width
		$rating_percent = '';
		if ( $type === 'output' ) {
			$rating_percent = 'width:' . ( $rating / $rating_input_count * 100 ) . '%;';
		} elseif ( $type === 'input' && ! $rating ) {
			// Default to 50% width for input with no rating
			$rating_percent = 'width:50%;';
		}

		// Build styles
		$foreground_style = ( $rating_percent || $rating_color ) ? "style='{$rating_percent}{$rating_color}'" : '';
		$background_style = "style='color:{$args['rating_color_off']};'";

		// Build rating wrap title
		$rating_wrap_title = '';
		if ( $type === 'output' ) {
			if ( $rating > 0 ) {
				$rating_wrap_title = wp_sprintf( __( '%d star rating', 'geodirectory' ), $rating );
			} else {
				$rating_wrap_title = __( 'No rating yet!', 'geodirectory' );
			}
			$rating_wrap_title = apply_filters( 'geodir_output_rating_title', $rating_wrap_title, $rating, $args );
		}
		$rating_wrap_title = $rating_wrap_title ? 'title="' . esc_attr( $rating_wrap_title ) . '"' : '';

		$wrap_class = $type === 'input' ? 'c-pointer' : '';

		ob_start();
		?>
		<div class="gd-rating-outer-wrap gd-rating-<?php echo esc_attr( $type ); ?>-wrap d-flex justify-content-between flex-nowrap w-100">
			<div class="gd-rating gd-rating-<?php echo esc_attr( $type ); ?> gd-rating-type-<?php echo esc_attr( $rating_type ); ?>">
				<span class="gd-rating-wrap d-inline-flex text-nowrap position-relative <?php echo esc_attr( $wrap_class ); ?>" <?php echo $rating_wrap_title; ?>>
					<span class="gd-rating-foreground position-absolute text-nowrap overflow-hidden" <?php echo $foreground_style; ?>><?php echo $rating_html; ?></span>
					<span class="gd-rating-background" <?php echo $background_style; ?>><?php echo $rating_html; ?></span>
				</span>
				<?php if ( $type === 'input' ) : ?>
					<span class="gd-rating-text badge text-body-emphasis bg-secondary-subtle border" data-title="<?php esc_attr_e( 'Select a rating', 'geodirectory' ); ?>"><?php esc_html_e( 'Select a rating', 'geodirectory' ); ?></span>
					<input type="hidden" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $rating ); ?>"/>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $rating_label ) ) : ?>
				<span class="gd-rating-label fw-bold p-0 m-0 text-nowrap"><?php echo esc_html( $rating_label ); ?></span>
			<?php endif; ?>
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
