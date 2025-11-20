<?php
namespace AyeCode\GeoDirectory\Core\Services;

class Taxonomies {


	/**
	 * Get all custom taxonomies.
	 *
	 * Refactored from geodir_get_taxonomies().
	 *
	 * @param string $post_type    The post type to filter by.
	 * @param bool   $include_tags Whether to include tag taxonomies. Default false.
	 *
	 * @return array Array of taxonomy slugs.
	 */
	public function get_taxonomies( string $post_type = '', bool $include_tags = false ): array {
		// Use the Settings service to get the option
		$taxonomies = geodirectory()->settings->get( 'taxonomies' );
		$gd_taxonomies = [];

		if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy => $args ) {

				// 1. Filter by Post Type (if requested)
				if ( $post_type !== '' ) {
					// If settings don't match the requested post type, skip.
					if ( ! isset( $args['object_type'] ) || $args['object_type'] !== $post_type ) {
						continue;
					}
				}

				// 2. Filter Tags (if not requested)
				// Legacy logic checked for "_tag" in the string
				if ( ! $include_tags && strpos( $taxonomy, '_tag' ) !== false ) {
					continue;
				}

				$gd_taxonomies[] = $taxonomy;
			}
		}

		/**
		 * Filter the taxonomies.
		 *
		 * @since 1.0.0
		 * @param array $gd_taxonomies The taxonomy array.
		 */
		return apply_filters( 'geodir_taxonomy', $gd_taxonomies );
	}

	/**
	 * Taxonomy Walker.
	 *
	 * Generates the HTML for category lists (Options, Checkboxes, or Radios).
	 * Refactored from GeoDir_Admin_Taxonomies::taxonomy_walker.
	 *
	 * @param string $taxonomy     The taxonomy slug.
	 * @param int    $parent       Parent term ID.
	 * @param int    $padding      Visual depth/padding level.
	 * @param array  $args         Configuration arguments:
	 * - display_type: 'select', 'multiselect', 'radio', 'checkbox'
	 * - selected: array of selected term IDs
	 * - exclude: array of term IDs to exclude
	 * - hide_empty: bool
	 *
	 * @return string HTML output.
	 */
	public function render_walker( $taxonomy, $parent = 0, $padding = 0, $args = [] ) {
		$defaults = [
			'display_type' => 'select',
			'selected'     => [],
			'exclude'      => [],
			'hide_empty'   => false,
		];
		$args = wp_parse_args( $args, $defaults );

		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'parent'     => $parent,
			'hide_empty' => $args['hide_empty'],
			'exclude'    => $args['exclude'],
		] );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}

		$output = '';
		$p_margin = $padding * 20;
		$next_padding = $padding + 1;
		$is_bs5 = isset( $GLOBALS['aui_bs5'] ) && $GLOBALS['aui_bs5'];

		// Wrapper for Checkbox/Radio groups (only on root level calls usually, but logic kept recursive)
		// In Field class we wrap the whole thing, so we might just need the items here.
		// Legacy logic added specific divs for parent/child. Let's keep it simple for the Input Field context.

		foreach ( $terms as $term ) {
			$term_name = geodirectory()->helpers->utf8_ucfirst( $term->name );
			$is_selected = in_array( $term->term_id, $args['selected'] );

			// --- RENDER: SELECT / MULTISELECT ---
			if ( in_array( $args['display_type'], [ 'select', 'multiselect' ] ) ) {
				$selected_attr = $is_selected ? 'selected="selected"' : '';
				$style = $p_margin > 0 ? 'style="margin-left:' . $p_margin . 'px;"' : '';
				$child_dash = $p_margin > 0 ? str_repeat( "-", $padding ) . ' ' : ''; // Visual dash for dropdowns

				$output .= sprintf(
					'<option value="%s" %s %s>%s%s</option>',
					esc_attr( $term->term_id ),
					$selected_attr,
					$style,
					$child_dash,
					esc_html( $term_name )
				);

			}
			// --- RENDER: RADIO / CHECKBOX ---
			else {
				$checked_attr = $is_selected ? 'checked="checked"' : '';
				$input_type = $args['display_type']; // 'radio' or 'checkbox'
				// For checkboxes/radios, WP expects tax_input[taxonomy][]
				$input_name = "tax_input[{$taxonomy}][]";

				$margin_class = $is_bs5 ? 'ms-' . ($padding * 3) : 'ml-' . ($padding * 3); // Bootstrap indentation
				$wrapper_style = $padding > 0 ? 'style="margin-left:' . $p_margin . 'px"' : ''; // Fallback inline

				$output .= '<div class="form-check" ' . $wrapper_style . '>';
				$output .= sprintf(
					'<input class="form-check-input" type="%s" name="%s" value="%s" id="gd-cat-%s" %s>',
					$input_type,
					$input_name,
					esc_attr( $term->term_id ),
					esc_attr( $term->term_id ),
					$checked_attr
				);
				$output .= sprintf(
					'<label class="form-check-label" for="gd-cat-%s">%s</label>',
					esc_attr( $term->term_id ),
					esc_html( $term_name )
				);
				$output .= '</div>';
			}

			// --- RECURSION ---
			// Fetch children
			$output .= $this->render_walker( $taxonomy, $term->term_id, $next_padding, $args );
		}

		return $output;
	}
}
