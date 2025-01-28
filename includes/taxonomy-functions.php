<?php
/**
 * Geodirectory Custom Post Types/Taxonomies
 *
 * Inits custom post types and taxonomies
 *
 * @package     GeoDirectory
 * @category    Core
 * @author      WPGeoDirectory
 */


/**
 * Get all custom taxonomies.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $post_type The post type.
 * @param bool $tages_taxonomies Is this a tag taxonomy?. Default: false.
 * @return array|bool Taxonomies on success. false on failure.
 * @todo moved to class
 */
function geodir_get_taxonomies($post_type = '', $tages_taxonomies = false)
{

    $taxonomies = array();
    $gd_taxonomies = array();

    if ($taxonomies = geodir_get_option('taxonomies')) {


        $gd_taxonomies = array_keys($taxonomies);


        if ($post_type != '')
            $gd_taxonomies = array();

        $i = 0;
        foreach ($taxonomies as $taxonomy => $args) {

            if ($post_type != '' && $args['object_type'] == $post_type)
                $gd_taxonomies[] = $taxonomy;

            if ($tages_taxonomies === false && strpos($taxonomy, '_tag') !== false) {
                if (array_search($taxonomy, $gd_taxonomies) !== false)
                    unset($gd_taxonomies[array_search($taxonomy, $gd_taxonomies)]);
            }

        }

        $gd_taxonomies = array_values($gd_taxonomies);
    }

    /**
     * Filter the taxonomies.
     *
     * @since 1.0.0
     * @param array $gd_taxonomies The taxonomy array.
     */
    $taxonomies = apply_filters('geodir_taxonomy', $gd_taxonomies);

    if (!empty($taxonomies)) {
        return $taxonomies;
    } else {
        return false;
    }
}


/**
 * Get categories dropdown HTML.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $post_type The post type.
 * @param string $selected The selected value.
 * @param bool $tages_taxonomies Is this a tag taxonomy?. Default: false.
 * @param bool $echo Prints the HTML when set to true. Default: true.
 * @return void|string Dropdown HTML.
 */
function  geodir_get_categories_dl($post_type = '', $selected = '', $is_tags = false, $echo = true)
{

    $tax = new GeoDir_Admin_Taxonomies();
    $html = $tax->get_category_select($post_type, $selected, $is_tags , $echo);

    if (!$echo)
        return $html;
}



/**
 * Get post type listing slug.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $object_type The post type.
 * @return bool|string Slug on success. false on failure.
 */
function geodir_get_listing_slug($object_type = '')
{

    $listing_slug = '';

    $post_types = geodir_get_posttypes('array');
    $taxonomies = geodir_get_option('taxonomies');


    if ($object_type != '') {
        if (!empty($post_types) && array_key_exists($object_type, $post_types)) {

            $object_info = $post_types[$object_type];
            $listing_slug = $object_info['listing_slug'];
        } elseif (!empty($taxonomies) && array_key_exists($object_type, $taxonomies)) {

            $temp_object_type = $object_type.'...'; // add '...' so we can ensure we are only stripping the last bit of the string.
            if(stripos(strrev($object_type), "sgat_") === 0){// its a tag
                $cpt = str_replace("_tags...","",$temp_object_type);
            }else{// its a cat
                $cpt = str_replace("category...","",$temp_object_type);
            }
            $listing_slug = $post_types[$cpt]['rewrite']['slug'];
        }

    }

    if (!empty($listing_slug))
        return $listing_slug;
    else
        return false;
}


/**
 * Get a taxonomy post type.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @param string $taxonomy The WordPress taxonomy string.
 * @return bool|string Post type on success. false on failure.
 */
function geodir_get_taxonomy_posttype($taxonomy = '')
{
    global $wp_query;

    $post_type = array();
    $taxonomies = array();

    if (!empty($taxonomy)) {
        $taxonomies[] = $taxonomy;
    } elseif (isset($wp_query->tax_query->queries)) {
        $tax_arr = $wp_query->tax_query->queries;
        //if tax query has 'relation' set then it will break wp_list_pluck so we remove it
        if(isset( $tax_arr['relation'])){unset( $tax_arr['relation']);}
        $taxonomies = wp_list_pluck($tax_arr, 'taxonomy');
    }

    if (!empty($taxonomies)) {
        foreach (geodir_get_posttypes() as $pt) {
            $object_taxonomies = $pt === 'attachment' ? get_taxonomies_for_attachments() : get_object_taxonomies($pt);
            if (array_intersect($taxonomies, $object_taxonomies))
                $post_type[] = $pt;
        }
    }

    if (!empty($post_type))
        return $post_type[0];
    else
        return false;
}

if ( ! function_exists( 'geodir_custom_taxonomy_walker' ) ) {
	/**
	 * Custom taxonomy walker function.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @param string $cat_taxonomy The taxonomy name.
	 * @param int $cat_parent The parent term ID.
	 * @param bool $hide_empty Hide empty taxonomies? Default: false.
	 * @param int $pading CSS padding in pixels.
	 * @return string|void taxonomy HTML.
	 */
	function geodir_custom_taxonomy_walker( $cat_taxonomy, $cat_parent = 0, $hide_empty = false, $pading = 0 ) {
		global $cat_display, $post_cat, $exclude_cats;

		$search_terms = ! is_null( $post_cat ) && $post_cat !== "" ? trim( $post_cat, "," ) : '';
		$search_terms = explode( ",", $search_terms );

		$cat_terms = get_terms( $cat_taxonomy, array( 'parent' => $cat_parent, 'hide_empty' => $hide_empty, 'exclude' => $exclude_cats ) );

		$display = '';
		$onchange = '';
		$term_check = '';
		$main_list_class = '';
		$out = '';

		// If there are terms, start displaying.
		if ( count( $cat_terms ) > 0 ) {
			// Displaying as a list.
			$p = $pading * 20;
			$pading++;

			if ( ( ! geodir_is_page( 'listing' ) ) || ( is_search() && $_REQUEST['search_taxonomy'] == '' ) ) {
				if ( $cat_parent == 0 ) {
					$list_class = 'main_list gd-parent-cats-list gd-cats-display-' . $cat_display;
					$main_list_class = 'class="main_list_selecter"';
				} else {
					//$display = 'display:none';
					$list_class = 'sub_list gd-sub-cats-list';
				}
			}

			if ( $cat_display == 'checkbox' || $cat_display == 'radio' ) {
				$p = 0;
				$out = '<div class="' . $list_class . ' gd-cat-row-' . $cat_parent . '" style="margin-left:' . $p . 'px;' . $display . ';">';
			}

			foreach ( $cat_terms as $cat_term ) {
				$term_name = geodir_utf8_ucfirst( $cat_term->name );

				$checked = '';

				if ( in_array( $cat_term->term_id, $search_terms ) ) {
					if ( $cat_display == 'select' || $cat_display == 'multiselect' ) {
						$checked = 'selected="selected"';
					} else {
						$checked = 'checked="checked"';
					}
				}

				if ( $cat_display == 'radio' ) {
					$out .= '<span style="display:block" ><input type="radio" field_type="radio" name="tax_input[' . $cat_term->taxonomy . '][]" ' . $main_list_class . ' alt="' . esc_attr( $cat_term->taxonomy ) . '" title="' . esc_attr( $term_name ) . '" value="' . (int) $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . (int) $cat_term->term_id . '" >' . $term_check . $term_name . '</span>';
				} else if ( $cat_display == 'select' || $cat_display == 'multiselect' ) {
					$out .= '<option ' . $main_list_class . ' style="margin-left:' . esc_attr( $p ) . 'px;" alt="' . esc_attr( $cat_term->taxonomy ) . '" title="' . esc_attr( $term_name ) . '" value="' . (int) $cat_term->term_id . '" ' . $checked . $onchange . ' >' . $term_check . $term_name . '</option>';
				} else {
					$out .= '<span style="display:block"><input style="display:inline-block" type="checkbox" field_type="checkbox" name="tax_input[' . $cat_term->taxonomy . '][]" ' . $main_list_class . ' alt="' . esc_attr( $cat_term->taxonomy ) . '" title="' . esc_attr( $term_name ) . '" value="' . (int) $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . esc_attr( $cat_term->term_id ) . '" >' . $term_check . $term_name . '</span>';
				}

				// Call recurson to print sub cats.
				$out .= geodir_custom_taxonomy_walker( $cat_taxonomy, $cat_term->term_id, $hide_empty, $pading );
			}

			if ( $cat_display == 'checkbox' || $cat_display == 'radio' ) {
				$out .= '</div>';
			}

			return $out;
		}

		return;
	}
}

/**
 * Get terms of a taxonomy as dropdown.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $cat_taxonomy The taxonomy name.
 * @param int $parrent The parent term ID. Default: 0.
 * @param bool|string $selected The selected value. Default: false.
 */
function geodir_get_catlist($cat_taxonomy, $parrent = 0, $selected = false)
{
    global $exclude_cats;

    $cat_terms = get_terms($cat_taxonomy, array('parent' => $parrent, 'hide_empty' => false, 'exclude' => $exclude_cats));

    if (!empty($cat_terms)) {
        $onchange = '';
        $onchange = ' onchange="show_subcatlist(this.value, this)" ';

        $option_selected = '';
        if (!$selected)
            $option_slected = ' selected="selected" ';

        echo '<select field_type="select" id="' . esc_attr($cat_taxonomy) . '" class="geodir-select" ' . $onchange . ' option-ajaxChosen="false" data-sortable="true">';

        echo '<option value="" ' . $option_selected . ' >' . __('Select Category', 'geodirectory') . '</option>';

        foreach ($cat_terms as $cat_term) {
            $option_selected = '';
            if ($selected == $cat_term->term_id)
                $option_selected = ' selected="selected" ';

            // Count child terms
            $child_terms = get_terms( $cat_taxonomy, array( 'parent' => $cat_term->term_id, 'hide_empty' => false, 'exclude' => $exclude_cats, 'number' => 1 ) );
            $has_child = !empty( $child_terms ) ? 't' : 'f';

            echo '<option  ' . $option_selected . ' alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" _hc="' . $has_child . '" >' . geodir_utf8_ucfirst($cat_term->name) . '</option>';
        }
        echo '</select>';
    }
}

/**
 * Retrieve the term link.
 *
 * @since 1.0.0
 *
 * @param string $termlink Term link URL.
 * @param object $term Term object.
 * @param string $taxonomy Taxonomy slug.
 * @return string The term link
 */
function geodir_get_term_link($termlink, $term, $taxonomy)
{
    return geodir_term_link($termlink, $term, $taxonomy); // taxonomy_functions.php
}
//add_filter('term_link', 'geodir_get_term_link', 10, 3);

/**
 * Retrieve the post type archive permalink.
 *
 * @since 1.0.0
 *
 * @param string $link The post type archive permalink.
 * @param string $post_type Post type name.
 * @return string The post type archive permalink.
 */
function geodir_get_posttype_link($link, $post_type)
{
    return geodir_posttype_link($link, $post_type); // taxonomy_functions.php
}
add_filter('post_type_archive_link', 'geodir_get_posttype_link', 10, 2);

/**
 * Returns the term link with parameters.
 *
 * @since 1.0.0
 * @since 1.5.7 Changes for the neighbourhood system improvement.
 * @since 1.6.11 Details page add locations to the term links.
 * @package GeoDirectory
 * @param string $termlink The term link
 * @param object $term Not yet implemented.
 * @param string $taxonomy The taxonomy name.
 * @return string The term link.
 */
function geodir_term_link($termlink, $term, $taxonomy) {
    $geodir_taxonomies = geodir_get_taxonomies('', true);

    if (isset($taxonomy) && !empty($geodir_taxonomies) && in_array($taxonomy, $geodir_taxonomies)) {
        global $geodir_add_location_url;
        $include_location = false;
        $request_term = array();
        $add_location_url = geodir_get_option('geodir_add_location_url');
        $location_manager = defined('GEODIR_LOCATIONS_TABLE') ? true : false;

        $listing_slug = geodir_get_listing_slug($taxonomy);

        if ($geodir_add_location_url != NULL && $geodir_add_location_url != '') {
            if ($geodir_add_location_url && $add_location_url) {
                $include_location = true;
            }
        } elseif ($add_location_url) {
            $include_location = true;
        } elseif ($add_location_url && $location_manager && geodir_is_page('detail')) {
            $include_location = true;
        }

        if ($include_location) {
            global $post;
            
            $neighbourhood_active = $location_manager && geodir_get_option('lm_enable_neighbourhoods') ? true : false;
            
            if (geodir_is_page('detail') && isset($post->country_slug)) {
                $location_terms = array(
                    'gd_country' => $post->country_slug,
                    'gd_region' => $post->region_slug,
                    'gd_city' => $post->city_slug
                );
                
                if ( $neighbourhood_active && !empty( $location_terms['gd_city'] ) && ( $gd_neighbourhood = get_query_var( 'gd_neighbourhood' ) ) ) {
                    $location_terms['gd_neighbourhood'] = $gd_neighbourhood;
                }
            } else {
                $location_terms = geodir_get_current_location_terms('query_vars');
            }

            $geodir_show_location_url = geodir_get_option('geodir_show_location_url');
            $location_terms = geodir_remove_location_terms($location_terms);

            if (!empty($location_terms)) {
                $url_separator = '';

                if (get_option('permalink_structure') != '') {
                    $old_listing_slug = '/' . $listing_slug . '/';
                    $request_term = implode("/", $location_terms);
                    $new_listing_slug = '/' . $listing_slug . '/' . $request_term . '/';

                    $termlink = substr_replace($termlink, $new_listing_slug, strpos($termlink, $old_listing_slug), strlen($old_listing_slug));
                } else {
                    $termlink = geodir_getlink($termlink, $request_term);
                }
            }
        }

        // Alter the CPT slug is WPML is set to do so
        /* we can replace this with the below function
        if(function_exists('icl_object_id')){
            global $sitepress;
            $post_type = str_replace("category","",$taxonomy);
            $termlink = $sitepress->post_type_archive_link_filter( $termlink, $post_type);
        }*/

		$termlink = apply_filters( 'geodir_term_link', $termlink, $term, $taxonomy );
    }
    
    return $termlink;
}


/**
 * Checks whether a term exists or not.
 *
 * Returns term data on success, bool when failure.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param int|string $term The term ID or slug.
 * @param string $taxonomy The taxonomy name.
 * @param int $parent Parent term ID.
 * @return bool|object Term data.
 */
function geodir_term_exists($term, $taxonomy = '', $parent = 0)
{
    global $wpdb;

    $select = "SELECT term_id FROM $wpdb->terms as t WHERE ";
    $tax_select = "SELECT tt.term_id, tt.term_taxonomy_id FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE ";

    if (is_int($term)) {
        if (0 == $term)
            return 0;
        $where = 't.term_id = %d';
        if (!empty($taxonomy))
            return $wpdb->get_row($wpdb->prepare($tax_select . $where . " AND tt.taxonomy = %s", $term, $taxonomy), ARRAY_A);
        else
            return $wpdb->get_var($wpdb->prepare($select . $where, $term));
    }

    $term = trim(wp_unslash($term));

    if ('' === $slug = sanitize_title($term))
        return 0;

    $where = 't.slug = %s';

    $where_fields = array($slug);
    if (!empty($taxonomy)) {
        $parent = (int)$parent;
        if ($parent > 0) {
            $where_fields[] = $parent;
            $else_where_fields[] = $parent;
            $where .= ' AND tt.parent = %d';

        }

        $where_fields[] = $taxonomy;


        if ($result = $wpdb->get_row($wpdb->prepare("SELECT tt.term_id, tt.term_taxonomy_id FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE $where AND tt.taxonomy = %s", $where_fields), ARRAY_A))
            return $result;

        return false;
    }

    if ($result = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM $wpdb->terms as t WHERE $where", $where_fields)))
        return $result;

    return false;
}

/**
 * Reset term icon values.
 *
 * @since 1.0.0
 * @package GeoDirectory
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
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param int|bool $term_id The term ID.
 * @param bool $rebuild Force rebuild the icons when set to true.
 * @return mixed|string|void Term icon(s).
 */
function geodir_get_term_icon( $term_id = false, $rebuild = false ) {
    global $wpdb;
    
    if ( !$rebuild ) {
        $terms_icons = geodir_get_option( 'gd_term_icons' );
    } else {
        $terms_icons = array();
    }
    
    if ( empty( $terms_icons ) ) {
        $post_types = geodir_get_posttypes();
        $terms_icons = array();
        $tax_arr = array();
        
        foreach ( $post_types as $post_type ) {
            $tax_arr[ $post_type . 'category' ] = $post_type;
        }
        
        $terms = $wpdb->get_results( "SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE taxonomy IN ('" . implode( "','", array_keys( $tax_arr ) ) . "')" );
        if ( !empty( $terms ) ) {
            $a_terms = array();
            foreach ( $terms as $term ) {
                $a_terms[ $tax_arr[ $term->taxonomy ] ][] = $term;
            }
            
            foreach ( $a_terms as $pt => $t2 ) {
                foreach ( $t2 as $term ) {
                    $terms_icons[ $term->term_id ] = geodir_get_cat_icon( $term->term_id, true, true );
                }
            }
        }
        
        geodir_update_option( 'gd_term_icons', $terms_icons );
    }
    
    if ( !empty( $term_id ) ) {
        if ( isset( $terms_icons[ $term_id ] ) ) {
            return $terms_icons[ $term_id ];
        } else {
            return GeoDir_Maps::default_marker_icon( true );
        }
    }
    
    if ( is_ssl() ) {
        $terms_icons = str_replace( "http:", "https:", $terms_icons );
    }
    
    return apply_filters( 'geodir_get_term_icons', $terms_icons, $term_id );
}

/**
 * Check given taxonomy belongs to GD.
 *
 * @since 2.0.0
 *
 * @param string $taxonomy The taxonomy.
 * @return bool True if given taxonomy belongs to GD., otherwise False.
 */
function geodir_is_gd_taxonomy( $taxonomy ) {
    global $gd_is_taxonomy;
    
    if ( empty( $taxonomy ) ) {
        return false;
    }
    
    if ( ! geodir_taxonomy_type( $taxonomy ) ) {
        return false;
    }
    
    if ( !empty( $gd_is_taxonomy ) && !empty( $gd_is_taxonomy[ $taxonomy ] ) ) {
        return true;
    }
    
    $gd_taxonomies = geodir_get_taxonomies( '', true );
    
    if ( !empty( $gd_taxonomies ) && in_array( $taxonomy, $gd_taxonomies ) ) {
        if ( !is_array( $gd_is_taxonomy ) ) {
            $gd_is_taxonomy = array();
        }
        
        $gd_is_taxonomy[ $taxonomy ] = true;
        
        return true;
    }
    
    return false;
}

/**
 * Check the type of GD taxonomy.
 * 
 * @param $taxonomy
 *
 * @return null|string
 */
function geodir_taxonomy_type( $taxonomy ) {
    global $gd_taxonomy_type;
    
    if ( empty( $taxonomy ) ) {
        return NULL;
    }
    
    if ( strpos( $taxonomy, 'gd_' ) !== 0 ) {
        return NULL;
    }
    
    if ( substr( $taxonomy , -8 ) == 'category' ) {
        return 'category';
    } else if ( substr( $taxonomy , -5 ) == '_tags' ) {
        return 'tag';
    }
    
    return NULL;
}

/**
 * Get the category icon url.
 *
 * @param $term_id
 * @param bool $full_path
 * @param bool $default
 *
 * @return mixed|void
 */
function geodir_get_cat_icon( $term_id, $full_path = false, $default = false ) {
    return GeoDir_Admin_Taxonomies::get_cat_icon($term_id,$full_path ,$default);
}

/**
 * Get the category icon alt text.
 *
 * @since 2.3.76
 *
 * @param int $term_id Category ID.
 * @param string|bool $default Default alt text. Default false.
 * @return string Alt text.
 */
function geodir_get_cat_icon_alt( $term_id, $default = false ) {
	return GeoDir_Admin_Taxonomies::get_cat_icon_alt( $term_id, $default );
}

/**
 * Get the category default image.
 *
 * @param $term_id
 * @param bool $full_path
 *
 * @return mixed|void
 */
function geodir_get_cat_image( $term_id, $full_path = false ) {
    return GeoDir_Admin_Taxonomies::get_cat_image($term_id,$full_path );
}

/**
 * Get the category top description html.
 *
 * @param $term_id
 *
 * @return mixed|void
 */
function geodir_get_cat_top_description( $term_id ) {
    return GeoDir_Admin_Taxonomies::get_cat_top_description($term_id);
}

/**
 * Get the category description html.
 *
 * @param int    $term_id The term ID.
 * @param string $type Description type.
 * @return mixed|void
 */
function geodir_category_description( $term_id, $type = 'top' ) {
    return GeoDir_Admin_Taxonomies::get_category_description( $term_id, $type );
}

/**
 * Get the taxonomy schemas.
 *
 * @return mixed|void
 */
function geodir_get_cat_schemas() {
    return GeoDir_Admin_Taxonomies::get_schemas();
}

/**
 * Function for recounting product terms, ignoring hidden products.
 *
 * @param array $terms
 * @param string $taxonomy
 * @param bool $callback
 * @param bool $terms_are_term_taxonomy_ids
 */
function geodir_term_recount( $terms, $taxonomy, $post_type, $callback = true, $terms_are_term_taxonomy_ids = true ) {
	global $wpdb;

	// Standard callback.
	if ( $callback ) {
		$id_parent_terms = $terms;
		if ( ! $terms_are_term_taxonomy_ids ) {
			// We passed in an array of TERMS in format id=>parent.
			$id_parent_terms = array_filter( (array) array_keys( $terms ) );
		}
		_update_post_term_count( $id_parent_terms, $taxonomy );
	}

	$exclude_term_ids = array();

	$query = array(
		'fields' => "SELECT COUNT( DISTINCT ID ) FROM {$wpdb->posts} p",
		'join'   => '',
		'where'  => "
			WHERE 1=1
			AND p.post_status = 'publish'
			AND p.post_type = '{$post_type}'
		",
	);

	if ( count( $exclude_term_ids ) ) {
		$query['join']  .= " LEFT JOIN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ( " . implode( ',', array_map( 'absint', $exclude_term_ids ) ) . " ) ) AS exclude_join ON exclude_join.object_id = p.ID";
		$query['where'] .= " AND exclude_join.object_id IS NULL";
	}

	// Pre-process term taxonomy ids.
	if ( ! $terms_are_term_taxonomy_ids ) {
		// We passed in an array of TERMS in format id=>parent.
		$terms = array_filter( (array) array_keys( $terms ) );
	} else {
		// If we have term taxonomy IDs we need to get the term ID.
		$term_taxonomy_ids = $terms;
		$terms             = array();
		foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {
			$term    = get_term_by( 'term_taxonomy_id', $term_taxonomy_id, $taxonomy->name );
			$terms[] = $term->term_id;
		}
	}

	// Exit if we have no terms to count.
	if ( empty( $terms ) ) {
		return;
	}

	// Ancestors need counting.
	if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
		foreach ( $terms as $term_id ) {
			$terms = array_merge( $terms, get_ancestors( $term_id, $taxonomy->name ) );
		}
	}

	// Unique terms only.
	$terms = array_unique( $terms );

	// Count the terms.
	foreach ( $terms as $term_id ) {
		$terms_to_count = array( absint( $term_id ) );

		if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
			// We need to get the $term's hierarchy so we can count its children too
			if ( ( $children = get_term_children( $term_id, $taxonomy->name ) ) && ! is_wp_error( $children ) ) {
				$terms_to_count = array_unique( array_map( 'absint', array_merge( $terms_to_count, $children ) ) );
			}
		}

		// Generate term query
		$term_query          = $query;
		$term_query['join'] .= " INNER JOIN ( SELECT object_id FROM {$wpdb->term_relationships} INNER JOIN {$wpdb->term_taxonomy} using( term_taxonomy_id ) WHERE term_id IN ( " . implode( ',', array_map( 'absint', $terms_to_count ) ) . " ) ) AS include_join ON include_join.object_id = p.ID";

		// Get the count
		$count = $wpdb->get_var( implode( ' ', $term_query ) );

		// Update the count
		update_term_meta( $term_id, '_gd_post_count_' . $taxonomy->name, absint( $count ) );
	}

	delete_transient( 'geodir_term_counts' );
}

/**
 * Get the all the child terms.
 *
 * @since 2.0.0.66
 *
 * @param int $child_of Parent term to get child terms.
 * @param string $taxonomy Taxonomy.
 * @param array $terms Array of terms. Default Empty.
 * @return array Array fo child terms.
 */
function geodir_get_term_children( $child_of, $taxonomy, $terms = array() ) {
	global $wpdb;

	if ( empty( $terms ) && $child_of > 0 ) {
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND tt.term_id = %d", array( $taxonomy, $child_of ) ) );
		if ( ! empty( $row ) ) {
			$terms[ $row->term_id ] = $row;
		}
	}

	$query = $wpdb->prepare( "SELECT t.*, tt.* FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND tt.parent = %d", array( $taxonomy, $child_of ) );
	$results = $wpdb->get_results( $query );

	if ( ! empty( $results ) ) {
		foreach ( $results as $i => $row ) {
			$terms[ $row->term_id ] = $row;

			if ( ! empty( $row->parent ) ) {
				$terms = geodir_get_term_children( $row->term_id, $taxonomy, $terms );
			}
		}
	}

	return $terms;
}

/**
 * Get the term post type.
 *
 * @since 2.0.0.68
 *
 * @param int $term_id The term id.
 * @return null|string Post type.
 */
function geodir_term_post_type( $term_id ) {
	$post_type = wp_cache_get( 'geodir_term_post_type:' . $term_id, 'geodir_term_post_type' );

	if ( $post_type !== false ) {
		return $post_type;
	}

	$post_type = '';
	$term = get_term( $term_id );
	if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
		$taxonomy = get_taxonomy( $term->taxonomy );

		if ( ! empty( $taxonomy ) && ! is_wp_error( $taxonomy ) && ! empty( $taxonomy->object_type ) ) {
			$post_type = $taxonomy->object_type[0];
		}
	}

	wp_cache_set( 'geodir_term_post_type:' . $term_id, $post_type, 'geodir_term_post_type' );

	return $post_type;
}