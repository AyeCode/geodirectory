<?php
/**
 * Geodirectory Custom Post Types/Taxonomies - Wrapper Functions
 *
 * Simple wrapper functions that call the new v3 Services and Utils.
 * All business logic has been moved to src/Core/Services/Taxonomies.php
 * and src/Core/Utils/Taxonomies.php.
 *
 * @package     GeoDirectory
 * @category    Core
 * @author      WPGeoDirectory
 * @since       1.0.0
 */


/**
 * Get all custom taxonomies.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param string $post_type        The post type.
 * @param bool   $tages_taxonomies Is this a tag taxonomy? Default: false.
 * @return array|bool Taxonomies on success, false on failure.
 */
function geodir_get_taxonomies( $post_type = '', $tages_taxonomies = false ) {
	$taxonomies = geodirectory()->taxonomies->get_taxonomies( $post_type, $tages_taxonomies );
	return ! empty( $taxonomies ) ? $taxonomies : false;
}


/**
 * Get categories dropdown HTML.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param string $post_type        The post type.
 * @param string $selected         The selected value.
 * @param bool   $tages_taxonomies Is this a tag taxonomy? Default: false.
 * @param bool   $echo             Prints the HTML when set to true. Default: true.
 * @return void|string Dropdown HTML.
 */
function geodir_get_categories_dl( $post_type = '', $selected = '', $is_tags = false, $echo = true ) {
	return geodirectory()->taxonomies->get_category_select( $post_type, $selected, $is_tags, $echo );
}


/**
 * Get post type listing slug.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param string $object_type The post type.
 * @return bool|string Slug on success, false on failure.
 */
function geodir_get_listing_slug( $object_type = '' ) {
	return geodirectory()->taxonomies->get_listing_slug( $object_type );
}


/**
 * Get a taxonomy post type.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param string $taxonomy The WordPress taxonomy string.
 * @return bool|string Post type on success, false on failure.
 */
function geodir_get_taxonomy_posttype( $taxonomy = '' ) {
	return geodirectory()->taxonomies->get_taxonomy_posttype( $taxonomy );
}


if ( ! function_exists( 'geodir_custom_taxonomy_walker' ) ) {
	/**
	 * Custom taxonomy walker function.
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Refactored to wrapper function.
	 *
	 * @param string $cat_taxonomy The taxonomy name.
	 * @param int    $cat_parent   The parent term ID.
	 * @param bool   $hide_empty   Hide empty taxonomies? Default: false.
	 * @param int    $pading       CSS padding in pixels.
	 * @return string|void taxonomy HTML.
	 */
	function geodir_custom_taxonomy_walker( $cat_taxonomy, $cat_parent = 0, $hide_empty = false, $pading = 0 ) {
		global $cat_display, $post_cat, $exclude_cats;

		$search_terms = ! is_null( $post_cat ) && $post_cat !== '' ? trim( $post_cat, ',' ) : '';
		$search_terms = explode( ',', $search_terms );

		$args = [
			'display_type' => $cat_display ?: 'select',
			'selected'     => $search_terms,
			'exclude'      => $exclude_cats ?: [],
			'hide_empty'   => $hide_empty,
		];

		return geodirectory()->taxonomies->render_walker( $cat_taxonomy, $cat_parent, $pading, $args );
	}
}


/**
 * Get terms of a taxonomy as dropdown.
 *
 * Legacy function - kept as-is for backward compatibility.
 *
 * @since 1.0.0
 * @param string     $cat_taxonomy The taxonomy name.
 * @param int        $parrent      The parent term ID. Default: 0.
 * @param bool|string $selected     The selected value. Default: false.
 */
function geodir_get_catlist( $cat_taxonomy, $parrent = 0, $selected = false ) {
	global $exclude_cats;

	$cat_terms = get_terms( $cat_taxonomy, [ 'parent' => $parrent, 'hide_empty' => false, 'exclude' => $exclude_cats ] );

	if ( ! empty( $cat_terms ) ) {
		$onchange = ' onchange="show_subcatlist(this.value, this)" ';
		$option_selected = '';

		if ( ! $selected ) {
			$option_slected = ' selected="selected" ';
		}

		echo '<select field_type="select" id="' . esc_attr( $cat_taxonomy ) . '" class="geodir-select" ' . $onchange . ' option-ajaxChosen="false" data-sortable="true">';
		echo '<option value="" ' . $option_selected . ' >' . __( 'Select Category', 'geodirectory' ) . '</option>';

		foreach ( $cat_terms as $cat_term ) {
			$option_selected = '';
			if ( $selected == $cat_term->term_id ) {
				$option_selected = ' selected="selected" ';
			}

			// Count child terms
			$child_terms = get_terms( $cat_taxonomy, [ 'parent' => $cat_term->term_id, 'hide_empty' => false, 'exclude' => $exclude_cats, 'number' => 1 ] );
			$has_child = ! empty( $child_terms ) ? 't' : 'f';

			echo '<option  ' . $option_selected . ' alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst( $cat_term->name ) . '" value="' . $cat_term->term_id . '" _hc="' . $has_child . '" >' . geodir_utf8_ucfirst( $cat_term->name ) . '</option>';
		}
		echo '</select>';
	}
}


/**
 * Retrieve the term link.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param string $termlink Term link URL.
 * @param object $term     Term object.
 * @param string $taxonomy Taxonomy slug.
 * @return string The term link.
 */
function geodir_get_term_link( $termlink, $term, $taxonomy ) {
	return geodir_term_link( $termlink, $term, $taxonomy );
}


/**
 * Retrieve the post type archive permalink.
 *
 * @since 1.0.0
 *
 * @param string $link      The post type archive permalink.
 * @param string $post_type Post type name.
 * @return string The post type archive permalink.
 */
function geodir_get_posttype_link( $link, $post_type ) {
	return geodir_posttype_link( $link, $post_type );
}
add_filter( 'post_type_archive_link', 'geodir_get_posttype_link', 10, 2 );


/**
 * Returns the term link with parameters.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param string $termlink The term link.
 * @param object $term     Term object.
 * @param string $taxonomy The taxonomy name.
 * @return string The term link.
 */
function geodir_term_link( $termlink, $term, $taxonomy ) {
	return geodirectory()->taxonomies->build_term_link( $termlink, $term, $taxonomy );
}


/**
 * Checks whether a term exists or not.
 *
 * Returns term data on success, bool when failure.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param int|string $term     The term ID or slug.
 * @param string     $taxonomy The taxonomy name.
 * @param int        $parent   Parent term ID.
 * @return bool|array|int Term data.
 */
function geodir_term_exists( $term, $taxonomy = '', $parent = 0 ) {
	return geodirectory()->taxonomies->term_exists( $term, $taxonomy, $parent );
}


/**
 * Reset term icon values.
 *
 * @since 1.0.0
 */
function geodir_get_term_icon_rebuild() {
	geodir_update_option( 'gd_term_icons', '' );
}


/**
 * Gets term icon using term ID.
 *
 * If term ID not passed returns all icons.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param int|bool $term_id The term ID.
 * @param bool     $rebuild Force rebuild the icons when set to true.
 * @return mixed|string|void Term icon(s).
 */
function geodir_get_term_icon( $term_id = false, $rebuild = false ) {
	return geodirectory()->taxonomies->get_term_icon( $term_id, $rebuild );
}


/**
 * Check given taxonomy belongs to GD.
 *
 * @since 2.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param string $taxonomy The taxonomy.
 * @return bool True if given taxonomy belongs to GD, otherwise False.
 */
function geodir_is_gd_taxonomy( $taxonomy ) {
	return geodirectory()->taxonomies->is_gd_taxonomy( $taxonomy );
}


/**
 * Check the type of GD taxonomy.
 *
 * @since 2.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param string $taxonomy The taxonomy.
 * @return null|string 'category', 'tag', or NULL.
 */
function geodir_taxonomy_type( $taxonomy ) {
	return \AyeCode\GeoDirectory\Core\Utils\Taxonomies::get_type( $taxonomy );
}


/**
 * Get the category icon url.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param int  $term_id   Term ID.
 * @param bool $full_path Get full path.
 * @param bool $default   Return default if not found.
 * @return string Category icon URL.
 */
function geodir_get_cat_icon( $term_id, $full_path = false, $default = false ) {
	return geodirectory()->taxonomies->get_cat_icon( $term_id, $full_path, $default );
}


/**
 * Get the category icon alt text.
 *
 * @since 2.3.76
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param int          $term_id Category ID.
 * @param string|bool $default  Default alt text. Default false.
 * @return string Alt text.
 */
function geodir_get_cat_icon_alt( $term_id, $default = false ) {
	return geodirectory()->taxonomies->get_cat_icon_alt( $term_id, $default );
}


/**
 * Get the category default image.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param int  $term_id   Term ID.
 * @param bool $full_path Get full path.
 * @return string Category image URL.
 */
function geodir_get_cat_image( $term_id, $full_path = false ) {
	return geodirectory()->taxonomies->get_cat_image( $term_id, $full_path );
}


/**
 * Get the category top description html.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param int $term_id Term ID.
 * @return string Top description HTML.
 */
function geodir_get_cat_top_description( $term_id ) {
	return geodirectory()->taxonomies->get_cat_top_description( $term_id );
}


/**
 * Get the category description html.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param int    $term_id Term ID.
 * @param string $type    Description type.
 * @return string Category description HTML.
 */
function geodir_category_description( $term_id, $type = 'top' ) {
	return geodirectory()->taxonomies->get_category_description( $term_id, $type );
}


/**
 * Get the taxonomy schemas.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @return array Schemas array.
 */
function geodir_get_cat_schemas() {
	return geodirectory()->taxonomies->get_schemas();
}


/**
 * Function for recounting product terms, ignoring hidden products.
 *
 * @since 1.0.0
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param array  $terms                       Terms array.
 * @param string $taxonomy                    Taxonomy name.
 * @param string $post_type                   Post type.
 * @param bool   $callback                    Use standard callback.
 * @param bool   $terms_are_term_taxonomy_ids Whether terms are term taxonomy IDs.
 */
function geodir_term_recount( $terms, $taxonomy, $post_type, $callback = true, $terms_are_term_taxonomy_ids = true ) {
	geodirectory()->taxonomies->term_recount( $terms, $taxonomy, $post_type, $callback, $terms_are_term_taxonomy_ids );
}


/**
 * Get the all the child terms.
 *
 * @since 2.0.0.66
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param int    $child_of Parent term to get child terms.
 * @param string $taxonomy Taxonomy.
 * @param array  $terms    Array of terms. Default Empty.
 * @return array Array of child terms.
 */
function geodir_get_term_children( $child_of, $taxonomy, $terms = [] ) {
	return geodirectory()->taxonomies->get_term_children( $child_of, $taxonomy, $terms );
}


/**
 * Get the term post type.
 *
 * @since 2.0.0.68
 * @since 3.0.0 Refactored to wrapper function.
 *
 * @param int $term_id The term id.
 * @return null|string Post type.
 */
function geodir_term_post_type( $term_id ) {
	return geodirectory()->taxonomies->get_term_post_type( $term_id );
}
