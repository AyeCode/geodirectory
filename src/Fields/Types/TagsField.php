<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

class TagsField extends AbstractFieldType {

	public function render_input() {
//		// Admin: Post Tags are handled by WP native metaboxes
//		if ( is_admin() ) {
//			return '';
//		}

		$args         = $this->get_aui_args();
		$extra_fields = $this->get_extra_fields();
		$post_type    = $this->field_data['post_type'];
		$taxonomy     = $post_type . '_tags'; // e.g. gd_place_tags

		// 1. Prepare Value (String -> Array)
		$value_str = $this->value; // "tag1, tag2"
		$value_arr = [];
		if ( ! empty( $value_str ) ) {
			$value_arr = array_map( 'trim', explode( ',', $value_str ) );
		}
		$args['value'] = $value_arr;

		// 2. Setup Select2 Attributes
		$args['select2']  = true;
		$args['multiple'] = true;

		// Placeholder
		if ( empty( $args['placeholder'] ) ) {
			$args['placeholder'] = __( 'Enter tags separated by a comma ,', 'geodirectory' );
		}
		$args['extra_attributes']['data-placeholder'] = $args['placeholder'];

		// Limits
		$package = \geodir_get_post_package( get_post( $this->post_id ), $post_type );
		$limit   = ! empty( $package->tag_limit ) ? absint( $package->tag_limit ) : 0;
		$limit   = (int) apply_filters( 'geodir_cfi_post_tags_limit', $limit, get_post( $this->post_id ), $package );

		if ( $limit > 0 ) {
			$args['extra_attributes']['data-maximum-selection-length'] = $limit;
		}

		// Tag Settings
		$args['extra_attributes']['data-tags']            = ( ! empty( $extra_fields['disable_new_tags'] ) ) ? 'false' : 'true';
		$args['extra_attributes']['spellcheck']           = ( ! empty( $extra_fields['spellcheck'] ) ) ? 'true' : 'false';
		$args['extra_attributes']['data-token-separators']= "[',']";

		// 3. Populate Options (Popular + Current)
		$options = [];

		// A. Fetch Popular Tags
		$tag_no = ! empty( $extra_fields['no_of_tag'] ) ? absint( $extra_fields['no_of_tag'] ) : 10;
		$tag_args = apply_filters( 'geodir_custom_field_input_tag_args', [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $tag_no,
		] );

		$popular_terms = get_terms( $tag_args );
		$popular_names = [];

		if ( ! empty( $popular_terms ) && ! is_wp_error( $popular_terms ) ) {
			$options[] = [ 'label' => __( 'Popular tags', 'geodirectory' ), 'optgroup' => 'start' ];

			foreach ( $popular_terms as $term ) {
				// Don't show in popular list if already selected (User experience preference)
				if ( in_array( $term->name, $value_arr ) ) {
					continue;
				}
				$options[] = [ 'label' => $term->name, 'value' => $term->name ];
				$popular_names[] = $term->name;
			}

			$options[] = [ 'optgroup' => 'end' ];
		}

		// B. Add Current Tags (Your Tags)
		// We need to ensure selected values exist in the options list for Select2 to render them correctly.
		if ( ! empty( $value_arr ) ) {
			$options[] = [ 'label' => __( 'Your tags', 'geodirectory' ), 'optgroup' => 'start' ];

			foreach ( $value_arr as $tag_name ) {
				$options[] = [ 'label' => $tag_name, 'value' => $tag_name ];
			}

			$options[] = [ 'optgroup' => 'end' ];
		}

		$args['options'] = $options;

		// Name must be array for taxonomy save logic in WP/GD
		$args['name'] = "tax_input[{$taxonomy}][]";

		return aui()->select( $args );
	}
}
