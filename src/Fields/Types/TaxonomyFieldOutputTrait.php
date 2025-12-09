<?php
/**
 * Taxonomy Field Output Rendering Trait
 *
 * Handles output rendering for taxonomy fields (categories and tags).
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

/**
 * Taxonomy field output methods.
 *
 * @since 3.0.0
 */
trait TaxonomyFieldOutputTrait {

	/**
	 * Render the output HTML for taxonomy field types.
	 *
	 * Replaces: geodir_cf_taxonomy()
	 *
	 * @param object|array $gd_post GeoDirectory post object with custom fields already loaded.
	 * @param array        $args    Output arguments:
	 *                              - 'show' (string|array): What to display.
	 *                              - 'location' (string): Output location.
	 * @return string
	 */
	public function render_output( $gd_post, $args = [] ) {
		// Use the $gd_post directly (no DB call needed - already has all custom fields!)
		if ( ! is_object( $gd_post ) ) {
			$gd_post = (object) $gd_post;
		}

		if ( empty( $gd_post ) ) {
			return '';
		}

		// Parse args with defaults
		$args = wp_parse_args( $args, [
			'show'     => '',
			'location' => '',
		] );

		$location = $args['location'];
		$html_var = $this->field_data['htmlvar_name'];

		// Parse output arguments (convert string to array)
		$output = $this->parse_output_args( $args['show'] );

		// Block demo content
		if ( $this->is_block_demo() ) {
			$gd_post = $this->set_demo_content( $gd_post, $html_var );
		}

		$html = '';

		// Apply custom filters first
		$html = $this->apply_output_filters( $html, $location, $output );

		// If filters provided custom HTML, return it
		if ( ! empty( $html ) ) {
			return $html;
		}

		// Get post info
		$post_id = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;

		if ( $post_id && wp_is_post_revision( $post_id ) ) {
			$post_id = wp_get_post_parent_id( $post_id );
		}

		$post_type = $post_id ? get_post_type( $post_id ) : ( isset( $gd_post->post_type ) ? $gd_post->post_type : 'gd_place' );

		// Determine taxonomy
		if ( $html_var == 'post_category' ) {
			$post_taxonomy = $post_type . 'category';
		} elseif ( $html_var == 'post_tags' ) {
			$post_taxonomy = $post_type . '_tags';
		} else {
			$post_taxonomy = '';
		}

		// Check if we have values
		if ( ! $post_taxonomy || empty( $gd_post->{$html_var} ) ) {
			return '';
		}

		$field_value = $gd_post->{$html_var};

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return $field_value;
		}

		// Parse term IDs/names
		$links = [];
		$terms = [];
		$terms_ordered = [];

		if ( ! is_array( $field_value ) ) {
			$field_value = explode( ',', trim( $field_value, ',' ) );
		}

		$field_value = array_unique( $field_value );

		if ( ! empty( $field_value ) ) {
			foreach ( $field_value as $term ) {
				$term = trim( $term );

				if ( $term != '' ) {
					// Tags use 'name', categories use 'id'
					$term_type = $html_var == 'post_tags' ? 'name' : 'id';
					$term_obj = get_term_by( $term_type, $term, $post_taxonomy );

					if ( is_object( $term_obj ) ) {
						$term_link = get_term_link( $term_obj, $post_taxonomy );
						if ( ! is_wp_error( $term_link ) ) {
							$links[] = '<a href="' . esc_url( $term_link ) . '">' . esc_html( $term_obj->name ) . '</a>';
							$terms[] = $term_obj;
						}
					}
				}
			}

			if ( ! empty( $links ) ) {
				// Order alphabetically
				asort( $links );
				foreach ( array_keys( $links ) as $key ) {
					$terms_ordered[ $key ] = $terms[ $key ];
				}
				$terms = $terms_ordered;
			}
		}

		// Create comma-separated list with proper grammar (wp_sprintf %l)
		$html_value = ! empty( $links ) && ! empty( $terms ) ? wp_sprintf( '%l', $links, (object) $terms ) : '';

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return $html_value;
		}

		if ( $html_value == '' ) {
			return '';
		}

		// Build full HTML output
		$icon_data = $this->process_icon();
		$field_icon_style = $icon_data['style'];
		$field_icon_html = $icon_data['icon_html'];

		$css_class = isset( $this->field_data['css_class'] ) ? $this->field_data['css_class'] : '';

		$html = '<div class="geodir_post_meta ' . esc_attr( $css_class ) . ' geodir-field-' . esc_attr( $html_var ) . '">';

		$maybe_secondary_class = isset( $output['icon'] ) ? 'gv-secondary' : '';

		// Icon
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon geodir-i-taxonomy" style="' . esc_attr( $field_icon_style ) . '">' . $field_icon_html;
		}

		// Label
		if ( $output == '' || isset( $output['label'] ) ) {
			$frontend_title = isset( $this->field_data['frontend_title'] ) ? trim( $this->field_data['frontend_title'] ) : '';
			if ( $frontend_title ) {
				$html .= '<span class="geodir_post_meta_title ' . esc_attr( $maybe_secondary_class ) . '">' . __( $frontend_title, 'geodirectory' ) . ': </span>';
			}
		}

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}

		// Value
		if ( $output == '' || isset( $output['value'] ) ) {
			$html .= $html_value;
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Set demo content for block editor.
	 *
	 * @param object $gd_post  Post object.
	 * @param string $html_var Field htmlvar_name.
	 * @return object Modified post object.
	 */
	protected function set_demo_content( $gd_post, $html_var ) {
		if ( $html_var == 'post_category' ) {
			$demo_tax = 'gd_placecategory';
		} elseif ( $html_var == 'post_tags' ) {
			$demo_tax = 'gd_place_tags';
		} else {
			return $gd_post;
		}

		$demo_terms_obj = get_terms( [
			'taxonomy'   => $demo_tax,
			'hide_empty' => false,
			'number'     => 2
		] );

		$demo_terms = '';
		if ( ! empty( $demo_terms_obj ) ) {
			foreach ( $demo_terms_obj as $demo_term ) {
				$demo_terms .= $demo_term->term_id . ',';
			}
		}

		$gd_post->{$html_var} = $demo_terms;
		$gd_post->post_type = 'gd_place';

		return $gd_post;
	}

	/**
	 * Helper methods from AbstractFieldOutput.
	 */
	abstract protected function parse_output_args( $args );
	abstract protected function apply_output_filters( $html, $location, $output );
	abstract protected function process_icon();
	abstract protected function is_block_demo();
}
