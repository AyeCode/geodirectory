<?php
/**
 * Post Listing functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

/**
 * Get post custom fields.
 *
 * @since 1.0.0
 * @since 2.3.14 Added $cached parameter.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 *
 * @param int|string $post_id Optional. The post ID.
 * @param bool       $cached Cached data. Default True.
 * @return object|bool Returns full post details as an object. If no details returns false.
 */
function geodir_get_post_info( $post_id = '', $cached = true ) {
	// Check for cache
	$cache = wp_cache_get( "gd_post_" . $post_id, 'gd_post' );

	if ( $cache && $cached ) {
		return $cache;
	}

	global $wpdb, $post, $post_info, $preview;

	if ( $post_id == '' && ! empty( $post ) ) {
		$post_id = $post->ID;
	}

	$post_type = get_post_type( $post_id );

	if ( $post_type == 'revision' ) {
		$post_type = get_post_type( wp_get_post_parent_id( $post_id ) );
	}

	// Check if preview
	if ( $preview && $post->ID == $post_id ) {
		$post_id = GeoDir_Post_Data::get_post_preview_id( $post_id );
	}

	if ( ! geodir_is_gd_post_type( $post_type ) ) {
		return new stdClass();
	}

	$table = geodir_db_cpt_table( $post_type );

	/**
	 * Apply Filter to change Post info
	 *
	 * You can use this filter to change Post info.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	$query = apply_filters( 'geodir_post_info_query', $wpdb->prepare( "SELECT p.*,pd.* FROM " . $wpdb->posts . " p," . $table . " pd WHERE p.ID = pd.post_id AND pd.post_id = %d", $post_id ) );

	$post_detail = $wpdb->get_row( $query );

	// Check for distance setting
	if ( ! empty( $post_detail ) && ! empty( $post->distance ) ) {
		$post_detail->distance = $post->distance;
	}

	if ( ! empty( $post_detail ) ) {
		/**
		 * Filter GeoDirectory post info object.
		 *
		 * @since 2.1.0.4
		 *
		 * @param object $post_detail The GeoDirectory post object.
		 * @param int    $post_id The post ID.
		 */
		$post_detail = apply_filters( 'geodir_get_post_info', $post_detail, $post_id );
	}

	$return = ! empty( $post_detail ) ? $post_info = $post_detail : $post_info = false;

	// Set cache
	if ( ! empty( $post_detail ) && $cached ) {
		wp_cache_set( "gd_post_" . $post_id, $post_detail, 'gd_post' );
	}

	return $return;
}

/**
 * Save or update post custom fields.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int $post_id The post ID.
 * @param string $postmeta Detail table column name.
 * @param string $meta_value Detail table column value.
 *
 * @return void|bool
 */
function geodir_save_post_meta( $post_id, $postmeta = '', $meta_value = '' ) {

	global $wpdb, $plugin_prefix;

	$post_type = get_post_type( $post_id );

	$table = $plugin_prefix . $post_type . '_detail';

	if ( $postmeta != '' && geodir_column_exist( $table, $postmeta ) && $post_id ) {

		if ( is_array( $meta_value ) ) {
			$meta_value = implode( ",", $meta_value );
		}

		if ( $wpdb->get_var( $wpdb->prepare( "SELECT post_id from " . $table . " where post_id = %d", array( $post_id ) ) ) ) {

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE " . $table . " SET " . $postmeta . " = '" . $meta_value . "' where post_id =%d",
					array( $post_id )
				)
			);

		} else {

			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO " . $table . " SET post_id = %d, " . $postmeta . " = '" . $meta_value . "'",
					array( $post_id )
				)
			);
		}

		// clear the post cache
		wp_cache_delete( "gd_post_" . $post_id, 'gd_post' );

	} else {
		return false;
	}
}


/**
 * Delete post custom fields.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int $post_id The post ID.
 * @param string $postmeta Detail table column name.
 *
 * @return bool
 */
function geodir_delete_post_meta( $post_id, $postmeta ) {

	global $wpdb, $plugin_prefix;

	$post_type = get_post_type( $post_id );

	$table = $plugin_prefix . $post_type . '_detail';

	// clear the post cache
	wp_cache_delete( "gd_post_" . $post_id, 'gd_post' );

	if ( is_array( $postmeta ) && ! empty( $postmeta ) && $post_id ) {
		$post_meta_set_query = '';

		foreach ( $postmeta as $mkey ) {
			if ( $mkey != '' ) {
				$post_meta_set_query .= $mkey . " = '', ";
			}
		}

		$post_meta_set_query = trim( $post_meta_set_query, ", " );

		if ( empty( $post_meta_set_query ) || trim( $post_meta_set_query ) == '' ) {
			return false;
		}

		if ( $wpdb->get_var( "SHOW COLUMNS FROM " . $table . " WHERE field = '" . $postmeta . "'" ) != '' ) {

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE " . $table . " SET " . $post_meta_set_query . " where post_id = %d",
					array( $post_id )
				)
			);

			return true;
		}

	} elseif ( $postmeta != '' && $post_id ) {
		if ( $wpdb->get_var( "SHOW COLUMNS FROM " . $table . " WHERE field = '" . $postmeta . "'" ) != '' ) {

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE " . $table . " SET " . $postmeta . "= '' where post_id = %d",
					array( $post_id )
				)
			);

			return true;
		}

	} else {
		return false;
	}
}


/**
 * Get post custom meta.
 *
 * @since 1.0.0
 * @since 1.6.20 Hook added to filter value.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int $post_id The post ID.
 * @param string $meta_key The meta key to retrieve.
 * @param bool $single Optional. Whether to return a single value. Default false.
 *
 * @todo single variable not yet implemented.
 * @return bool|mixed|null|string Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
if ( ! function_exists( 'geodir_get_post_meta' ) ) {
function geodir_get_post_meta( $post_id, $meta_key, $single = false ) {
	if ( ! $post_id ) {
		return false;
	}
	global $wpdb, $plugin_prefix, $preview;

	$all_postypes = geodir_get_posttypes();

	$post_type = get_post_type( $post_id );

	if ( $post_type == 'revision' ) {
		$post_type = get_post_type( wp_get_post_parent_id( $post_id ) );
	}

	// check if preview
	if ( $preview ) {
		$post_id = GeoDir_Post_Data::get_post_preview_id( $post_id );
	}

	if ( ! in_array( $post_type, $all_postypes ) ) {
		return false;
	}

	/**
	 * Short circuit the DB query if needed.
	 */
	$pre_value = apply_filters( 'geodir_pre_get_post_meta', null, $post_id, $meta_key, $single );
	if($pre_value!==null){
		return $pre_value;
	}

	$table = $plugin_prefix . $post_type . '_detail';

	if ( $table && $meta_key ) {
		//if ( $wpdb->get_var( "SHOW COLUMNS FROM " . $table . " WHERE field = '" . $meta_key . "'" ) != '' ) {
		$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT `" . $meta_key . "` from " . $table . " where post_id = %d", array( $post_id ) ) );

		if ( ($meta_value || $meta_value==='0') && $meta_value !== '' ) {
			$meta_value = maybe_serialize( $meta_value );
		}else{
			$meta_value = false;
		}
	} else {
		$meta_value = false;
	}

	/**
	 * Filter the listing custom meta.
	 *
	 * @since 1.6.20
	 *
	 * @param bool|mixed|null|string $meta_value Will be an array if $single is false. Will be value of meta data field if $single is true.
	 * @param int $post_id The post ID.
	 * @param string $meta_key The meta key to retrieve.
	 * @param bool $single Optional. Whether to return a single value. Default false.
	 */
	return apply_filters( 'geodir_get_post_meta', $meta_value, $post_id, $meta_key, $single );
}
}

/**
 * Checks if a given post has a given custom meta.
 *
 * @since 2.0.0.65
 * @package GeoDirectory
 * @global object $post Current post object.
 *
 * @param string $meta_key The meta key to check.
 * @param int $post_id Optional. Defaults to current post. The post whose meta should be checked.
 *
 * @return bool True if the key exists and has a non-empty value. False otherwise.
 */
if ( ! function_exists( 'geodir_has_post_meta' ) ) {
	function geodir_has_post_meta( $meta_key, $post_id = null ) {
		global $post;

		//Use the current post's id if none is provided
		if( is_null( $post_id ) ) {
			$post_id = $post->ID;
		}

		return !empty( geodir_get_post_meta( $post_id, $meta_key, true ) );

	}
}

/**
 * Default post status for new posts.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @return string Returns the default post status for new posts. Ex: draft, publish etc.
 */
function geodir_new_post_default_status() {
	return GeoDir_Post_Data::get_post_default_status();
}

/**
 * This function would display the html content for add to favorite or remove from favorite.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @global object $current_user Current user object.
 * @global object $post The current post object.
 *
 * @param int $user_id User id.
 * @param int $post_id Post id.
 *
 * @return string
 */
function geodir_favourite_html( $user_id, $post_id, $args = array() ) {
	global $current_user, $aui_bs5, $post;

	if ( isset( $post->post_type ) && $post->post_type ) {
		$post_type = $post->post_type;
	} else {
		$post_type = get_post_type( $post_id );
	}

	if ( geodir_cpt_has_favourite_disabled( $post_type ) ) {
		return '';
	}

	/**
	 * Filter to modify "Add to Favorites" text
	 *
	 * You can use this filter to rename "Add to Favorites" text to something else.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	$add_favourite_text = apply_filters( 'geodir_add_favourite_text', __( 'Add to Favorites', 'geodirectory' ) );

	/**
	 * Filter to modify "Favourite" text
	 *
	 * You can use this filter to rename "Favourite" text to something else.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	$favourite_text = apply_filters( 'geodir_favourite_text', __( 'Favorite', 'geodirectory' ) );

	/**
	 * Filter to modify "Unfavorite" text
	 *
	 * You can use this filter to rename "Unfavorite" text to something else.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	$remove_favourite_text = apply_filters( 'geodir_remove_favourite_text', __( 'Remove from Favorites', 'geodirectory' ) );

	/**
	 * Filter to modify "Remove from Favorites" text
	 *
	 * You can use this filter to rename "Remove from Favorites" text to something else.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	$unfavourite_text = apply_filters( 'geodir_unfavourite_text', __( 'Unfavorite', 'geodirectory' ) );

	/**
	 * Filter to modify "fas fa-heart" icon
	 *
	 * You can use this filter to change "fas fa-heart" icon to something else.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	$favourite_icon = apply_filters( 'geodir_favourite_icon', 'fas fa-heart' );

	/**
	 * Filter to modify "fas fa-heart" icon for "remove from favorites" link
	 *
	 * You can use this filter to change "fas fa-heart" icon to something else.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	$unfavourite_icon = apply_filters( 'geodir_unfavourite_icon', 'fas fa-heart' );

	// set icon if user set
	if ( ! empty( $args['icon'] ) ) {
		$unfavourite_icon = $favourite_icon = esc_attr( $args['icon'] );
	}

	// set colour
	$icon_color_off = !empty($args['icon_color_off']) ? esc_attr($args['icon_color_off']) : 'grey';
	$icon_color_on = !empty($args['icon_color_on']) ? esc_attr($args['icon_color_on']) : '#e84739';

	$user_meta_data = '';
	if ( isset( $current_user->data->ID ) ) {
		$user_meta_data = geodir_get_user_favourites( $current_user->data->ID );
	}

	$design_style = geodir_design_style();
	$template = $design_style ? $design_style."/fav/fav.php" : "legacy/fav/fav.php";

	$show = isset($args['show']) ? esc_attr($args['show']) : '';
	$post_id = absint($post_id);
	$text_style = '';
	$text_class = '';
	$wrap_class = '';
	$icon_style = '';
	$link_style = '';

	if ( ! empty( $user_meta_data ) && in_array( $post_id, $user_meta_data ) ) {
		$link_class = 'geodir-removetofav-icon c-pointer geodir-act-fav';
		$onclick = "javascript:gd_fav_save($post_id);";
		$title = $remove_favourite_text;
		$icon_class = !empty($args['icon']) ? esc_attr($args['icon']) : $favourite_icon;
		$icon_style .= $icon_color_on ? "color:$icon_color_on;" : '';
		$text = $unfavourite_text;
	} else {
		if ( ! isset( $current_user->data->ID ) || $current_user->data->ID == '' ) {
			$onclick = 'javascript:window.location.href=\'' . geodir_login_url() . '\'';
		} else {
			$onclick = 'javascript:gd_fav_save(' . $post_id . ')';
		}

		$link_class = 'geodir-addtofav-icon c-pointer geodir-act-fav';
		$title = $add_favourite_text;
		$icon_class = !empty($args['icon']) ? esc_attr($args['icon']) : $favourite_icon;
		$icon_style .= $icon_color_off ? "color:$icon_color_off;" : '';
		$text = $favourite_text;
	}

	if($design_style){
		// type
		if($args['type'] == 'badge'){$link_class .= ' badge';}
		if($args['type'] == 'pill'){$link_class .= ' badge ' . ( $aui_bs5 ? 'rounded-pill' : 'badge-pill' );}

		// shadow
		if(!empty($args['shadow'])){
			if($args['shadow']=='small'){$link_class .= ' shadow-sm';}
			elseif($args['shadow']=='medium'){$link_class .= ' shadow';}
			elseif($args['shadow']=='large'){$link_class .= ' shadow-lg';}
		}

		// size
		if(!empty($args['size'])){
			$wrap_class .= ' '.sanitize_html_class($args['size']);
		}

		// bg color
		if ( ! empty( $args['color'] ) ) {
			$link_class .= ( $aui_bs5 ? ' bg-' : ' badge-' ) . sanitize_html_class( $args['color'] );
		} elseif ( ! empty( $args['bg_color'] ) && $args['type'] != 'link' ) {
			$link_style .= "background:" . esc_attr( $args['bg_color'] ) . ";";
		}

		// text color
		if( !empty($args['txt_color'])){
			$text_style .= "color:".esc_attr($args['txt_color']).";";
		}

		// position
		if( !empty($args['alignment']) && $args['alignment']=='block' ){
			$link_class .= " w-100 ";
		}
	}

	$text_color = ! empty( $args['txt_color'] ) ? esc_attr( $args['txt_color'] ) : '';

	$output = geodir_get_template_html( $template, array(
		// pass whats used
		'post_id'   => $post_id,
		'link_class'   => $link_class,
		'onclick'   => $onclick,
		'title'     => $title,
		'icon_class'      => $icon_class,
		'text_class'      => $text_class,
		'wrap_class'      => $wrap_class,
		'link_style'      => $link_style,
		'icon_style'      => $icon_style,
		'text_style'      => $text_style,
		'text_color'      => $text_color,
		'text'      => $text,
		'show'  => $show,
		// pass everything so it can be customised easily.
		'args'  => $args,
		'user_meta_data'    => $user_meta_data,
		'icon_color_on' => $icon_color_on,
		'icon_color_off' => $icon_color_off,
		'custom_icon' => !empty($args['icon']) ? esc_attr($args['icon']) : '',
		'add_favourite_text' => $add_favourite_text,
		'remove_favourite_text' => $remove_favourite_text,
		'unfavourite_text' => $unfavourite_text,
		'favourite_text' => $favourite_text,
		'favourite_icon' => $favourite_icon,
		'unfavourite_icon' => $unfavourite_icon,
	) );

	echo $output;
}

/**
 * Get listing author id.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int|string $listing_id The post ID.
 *
 * @return string|int The author ID.
 */
function geodir_get_listing_author( $listing_id = '' ) {
	if ( $listing_id == '' ) {
		if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
			$listing_id = $_REQUEST['pid'];
		}
	}
	$listing           = get_post( strip_tags( $listing_id ) );
	$listing_author_id = $listing->post_author;

	return $listing_author_id;
}


/**
 * Check whether a listing belongs to a user or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int|string $listing_id The post ID.
 * @param int $user_id The user ID.
 *
 * @return bool
 */
function geodir_lisiting_belong_to_user( $listing_id, $user_id ) {
	if ( empty( $listing_id ) || empty( $user_id ) ) {
		return false;
	}

	$listing_author_id = geodir_get_listing_author( $listing_id );

	if ( $listing_author_id == $user_id ) {
		return true;
	} else {
		return false;
	}

}

/**
 * Check whether a listing belongs to current user or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $current_user Current user object.
 *
 * @param int|string $listing_id The post ID.
 * @param bool $exclude_admin Optional. Do you want to exclude admin from the check?. Default true.
 *
 * @return bool
 */
function geodir_listing_belong_to_current_user( $listing_id = '', $exclude_admin = true ) {
	global $current_user;
	if ( $exclude_admin ) {
		foreach ( $current_user->caps as $key => $caps ) {
			if ( geodir_strtolower( $key ) == 'administrator' ) {
				return true;
				break;
			}
		}
	}

	$belong_to_user = geodir_lisiting_belong_to_user( $listing_id, $current_user->ID );

	/**
	 * Filter whether a listing belongs to current user or not.
	 *
	 * @since 2.0.0.65
	 *
	 * @param bool $belong_to_user True if a listing belongs to current user or False.
	 * @param int|string $listing_id The post ID.
	 * @param bool $exclude_admin If True it excludes admin from the check.
	 */
	return apply_filters( 'geodir_listing_belong_to_current_user', $belong_to_user, $listing_id, $exclude_admin );
}

/**
 * Called when post updated.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @global array $geodir_post_published Post ids being published.
 *
 * @param int $post_ID The post ID.
 * @param object $post_after The post object after update.
 * @param object $post_before The post object before update.
 */
function geodir_function_post_updated( $post_ID, $post_after, $post_before ) {
	global $geodir_post_published;

	$post_type = get_post_type( $post_ID );

	if ( $post_type != '' && in_array( $post_type, geodir_get_posttypes() ) ) {
		// send notification to client when post moves from draft to publish
		if ( ! empty( $post_after->post_status ) && in_array( $post_after->post_status, geodir_get_publish_statuses( array( 'post_type' => $post_type ) ) ) && ! empty( $post_before->post_status ) && ! in_array( $post_before->post_status, geodir_get_publish_statuses( array( 'post_type' => $post_type ) ) ) && $post_before->post_status != 'trash' ) {
			$gd_post = geodir_get_post_info( $post_ID );

			if ( empty( $gd_post ) ) {
				return;
			}

			if ( ! is_array( $geodir_post_published ) ) {
				$geodir_post_published = array();
			}

			// post_updated executed before data saved in detail table.
			$geodir_post_published[ $post_ID ] = $post_ID;
		}
	}
}
add_action( 'post_updated', 'geodir_function_post_updated', 16, 3 );

/**
 * Adds the featured image to the place details page header so facebook can use it when sharing the link.
 *
 * @since 1.4.9
 * @package GeoDirectory
 */
function geodir_fb_like_thumbnail() {

	// return if not a single post
	if ( ! is_single() ) {
		return;
	}

	global $gd_post;
	if ( isset( $gd_post->featured_image ) && $gd_post->featured_image ) {
		$upload_dir = wp_upload_dir();
		$thumb      = $upload_dir['baseurl'] . $gd_post->featured_image;
		echo "\n\n<!-- GD Facebook Like Thumbnail -->\n<link rel=\"image_src\" href=\"$thumb\" />\n<!-- End GD Facebook Like Thumbnail -->\n\n";

	}
}
add_action( 'wp_head', 'geodir_fb_like_thumbnail' );

/**
 * Get custom statuses.
 *
 * @since 2.0.0
 * @since 2.1.1.5 $post_type parameter added.
 *
 * @param string $post_type The post type. Default empty.
 * @return array $custom_statuses.
 */
function geodir_get_custom_statuses( $post_type = '' ) {
	$custom_statuses = array(
		'gd-closed' => _x( 'Closed down', 'Listing status', 'geodirectory' )
	);

	return apply_filters( 'geodir_listing_custom_statuses', $custom_statuses, $post_type );
}

/**
 * Get post statuses.
 *
 * @since 2.0.0
 * @since 2.1.1.5 $post_type parameter added.
 *
 * @return array $statuses.
 */
function geodir_get_post_statuses( $post_type = '' ) {
	$default_statuses = get_post_statuses();
	$custom_statuses  = geodir_get_custom_statuses( $post_type );

	$statuses = array_merge( $default_statuses, $custom_statuses );

	return apply_filters( 'geodir_post_statuses', $statuses );
}

/**
 * @since 2.1.1.5.
 */
function geodir_register_custom_statuses() {
	$statuses = array(
		'gd-closed'    => array(
			'label'                     => _x( 'Closed down', 'Listing status', 'geodirectory' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Closed down <span class="count">(%s)</span>', 'Closed down <span class="count">(%s)</span>', 'geodirectory' ),
		)
	);

	return apply_filters( 'geodir_register_post_statuses', $statuses );
}

/**
 * Get list of statuses which are consider 'publish'.
 *
 * @since  2.1.1.5
 * @return array
 */
function geodir_get_publish_statuses( $args = array() ) {
	return apply_filters( 'geodir_get_publish_statuses', array( 'publish' ), $args );
}

/**
 * Get list of statuses which are consider 'pending'.
 *
 * @since  2.1.1.5
 * @return array
 */
function geodir_get_pending_statuses( $args = array() ) {
	return apply_filters( 'geodir_get_pending_statuses', array( 'pending' ), $args );
}

/**
 * Get a list of post statuses.
 *
 * @since 2.1.1.5.
 *
 * @param string $context The context.
 * @param string $args The args.
 * @return array Post statuses.
 */
function geodir_get_post_stati( $context, $args = array() ) {
	$statuses = array();
	$publish_statuses = geodir_get_publish_statuses( $args );

	switch( $context ) {
		case 'author-archive':
		case 'widget-listings-author':
			$custom_statuses = geodir_register_custom_statuses();

			if ( ! empty( $custom_statuses ) ) {
				$publish_statuses = array_merge( $publish_statuses, array_keys( $custom_statuses ) );
			}

			$statuses = array_merge( $publish_statuses, array( 'pending', 'draft', 'private', 'future' ) );
			break;
		case 'search':
			$statuses = $publish_statuses;
			break;
		case 'single-map':
			$statuses = array_merge( $publish_statuses, array( 'pending', 'draft', 'inherit', 'auto-draft' ) );

			$non_public_statuses = geodir_get_post_stati( 'non-public', $args );

			if ( ! empty( $non_public_statuses ) && is_array( $non_public_statuses ) ) {
				$statuses = array_merge( $statuses, $non_public_statuses );
			}
			break;
		case 'map':
			$statuses = $publish_statuses;
			break;
		case 'non-public':
			$custom_statuses = geodir_register_custom_statuses();

			foreach ( $custom_statuses as $status => $data ) {
				if ( isset( $data['public'] ) && $data['public'] === false ) {
					$statuses[] = $status;
				}
			}
			break;
		case 'widget-listings':
			$statuses = $publish_statuses;

			if ( current_user_can( 'manage_options' ) ) {
				//$statuses[] = 'private'; // i don't see how this is useful on the front end, it really slows the query down for admins doing testing (stiofan)
			}
			break;
		case 'import':
			$statuses = array_keys( geodir_get_post_statuses( ( ! empty( $args['post_type'] ) ? $args['post_type'] : '' ) ) );
			break;
		case 'posts-count-live':
			$statuses = $publish_statuses;
			break;
		case 'posts-count-offline':
			$statuses = geodir_get_post_stati( 'non-public', $args );
			$statuses = array_merge( $statuses, array( 'pending', 'draft', 'private', 'future' ) );
			break;
		case 'unpublished':
			$statuses = array( 'pending', 'draft', 'auto-draft', 'trash' );
			break;
		default:
			$statuses = $publish_statuses;
			break;
	}

	$statuses = apply_filters( 'geodir_get_post_stati', $statuses, $context, $args );

	if ( ! empty( $statuses ) ) {
		$statuses = array_unique( $statuses );
	}

	return $statuses;
}

/**
 * Get the nice name for an listing status.
 *
 * @since  2.0.0
 *
 * @param  string $status
 *
 * @return string
 */
function geodir_get_post_status_name( $status ) {
	$statuses = geodir_get_post_statuses();
	if ( ! empty( $statuses ) && isset( $statuses[ $status ] ) ) {
		$status_name = $statuses[ $status ];
	} else {
		$status_object = get_post_status_object( $status );
		if ( ! empty( $status_object->label ) ) {
			$status_name = $status_object->label;
		} else {
			$status_name = $status;
		}
	}

	return $status_name;
}

/**
 * Post is closed.
 *
 * @since 2.0.0
 *
 * @param object $post Post object.
 *
 * @return bool $closed
 */
function geodir_post_is_closed( $post ) {
	if ( empty( $post ) ) {
		return false;
	}

	$status = ! empty( $post->post_status ) ? $post->post_status : get_post_status( $post );
	$closed = $status == 'gd-closed' ? true : false;

	return apply_filters( 'geodir_post_is_closed', $closed, $post );
}


/**
 * Returns the edit post link.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */
function geodir_edit_post_link( $post_id = '' ) {
	if ( ! $post_id ) {
		global $post;

		$post_id = $post->ID;
	}

	return geodir_add_listing_page_url( get_post_type( $post_id ), $post_id );
}

/**
 * Setup $gd_post variable.
 *
 * @since 2.0.0
 *
 * @param int $the_post the post.
 */
function geodir_setup_postdata( $the_post ) {
	global $post;

	if ( is_int( $the_post ) && $the_post > 0 ) {
		$the_post = geodir_get_post_info( $the_post );
	} else if ( is_object( $the_post ) ) {
		if ( ! isset( $the_post->post_category ) ) {
			$post_id  = isset( $the_post->ID ) ? $the_post->ID : $post->ID;
			$the_post = geodir_get_post_info( $post_id );
		}
	}

	if ( empty( $the_post->ID ) ) {
		return;
	}

	$GLOBALS['gd_post'] = $the_post;

	if ( empty( $post ) ) {
		$post = get_post( $the_post->ID );
		setup_postdata( $post );
		$GLOBALS['post'] = $post;
	} else if ( ! empty( $post ) && $post->ID != $the_post->ID ) {
		setup_postdata( $the_post->ID );
		if ( $post->ID != $the_post->ID ) {
			$GLOBALS['post'] = get_post( $the_post->ID );
		}
	}
}

/**
 * Get post badge.
 *
 * @since 2.0.0
 *
 * @param int $post_id Post id/
 * @param array $args Optional. Post arguments. Default array.
 *
 * @global object $gd_post GD post object.
 *
 * @return string $output.
 */
function geodir_get_post_badge( $post_id ='', $args = array() ) {
	global $aui_bs5, $gd_post;

	$output = '';
	if ( empty( $post_id ) ) {
		//return $output;
	}

	$post_type = $post_id ? get_post_type( $post_id ) : '';

	// check if its demo content
	if ($post_type &&  $post_type == 'page' && geodir_is_block_demo() ) {
		$post_type = 'gd_place';
	}

	if ($post_type &&  ! geodir_is_gd_post_type( $post_type ) ) {
		return $output;
	}

	$design_style = geodir_design_style();

	$defaults = array(
		'key'       => '',
		'condition' => '',
		'search'    => 'is_equal',
		'badge'     => '',
		'link'     => '',
		'new_window'     => '',
		'bg_color'  => '#0073aa',
		'txt_color' => '#ffffff',
		'size'      => '',
		'alignment' => '',
		'css_class' => '',
		'onclick'   => '',
		'icon_class'=> '',
		'extra_attributes'=> '', // 'data-save-list-id=123 data-other-post-id=321', AUI is array
		'tag'       => '',
		'popover_title'=> '',
		'popover_text'=> '',
		'cta'=> '', // click through action
		'tooltip_text'  => '',
		'hover_content'  => '',
		'hover_icon'  => '',
		'type'=> '', // AUI only
		'color'=> '', // AUI only
		'shadow'=> '', // AUI only
		'preview'=> '', // AUI only
	);

	$args = shortcode_atts( $defaults, $args, 'gd_post_badge' );

	if ( $args['key'] == 'street' ) {
		$args['key'] = 'address';
	}

	$match_field = $_match_field = $args['key'];
	if ( $match_field == 'address' ) {
		$match_field = 'street';
	} elseif ( $match_field == 'post_images' ) {
		$match_field = 'featured_image';
	}

	$find_post = ( ! empty( $gd_post->ID ) && $gd_post->ID == $post_id ) ? $gd_post : geodir_get_post_info( $post_id );
	$find_post_keys = ! empty( $find_post ) ? array_keys( (array) $find_post ) : array();

	if ( ! empty( $find_post->ID ) && ! in_array( 'post_category', $find_post_keys ) ) {
		$find_post = geodir_get_post_info( (int) $find_post->ID );
		$find_post_keys = ! empty( $find_post ) ? array_keys( (array) $find_post ) : array();
	}

	if ( $match_field === '' || ( ! empty( $find_post_keys ) && ( in_array( $match_field, $find_post_keys ) || in_array( $_match_field, $find_post_keys ) ) ) ) {
		$address_fields = array( 'street2', 'neighbourhood', 'city', 'region', 'country', 'zip', 'latitude', 'longitude' ); // Address fields
		$field = array();
		$badge = $args['badge'];

		// Check if there is a specific filter for field.
		if ( has_filter( 'geodir_output_badge_field_key_' . $match_field ) ) {
			$output = apply_filters( 'geodir_output_badge_field_key_' . $match_field, $output, $find_post, $args );
		}

		if ( $match_field && ! in_array( $match_field, array( 'post_date', 'post_modified', 'default_category', 'post_id', 'post_status' ) ) && ! in_array( $match_field, $address_fields ) ) {
			$package_id = geodir_get_post_package_id( $post_id, $post_type );
			$fields = geodir_post_custom_fields( $package_id, 'all', $post_type, 'none' );

			foreach ( $fields as $field_info ) {
				if ( $match_field == $field_info['htmlvar_name'] ) {
					$field = $field_info;
					break;
				} elseif( $_match_field == $field_info['htmlvar_name'] ) {
					$field = $field_info;
					break;
				}
			}

			if ( ! empty( $field ) ) {
				// Check if there is a specific filter for key type.
				if ( has_filter( 'geodir_output_badge_key_' . $field['field_type_key'] ) ) {
					$output = apply_filters( 'geodir_output_badge_key_' . $field['field_type_key'], $output, $find_post, $args, $field );
				}

				// Check if there is a specific filter for condition.
				if ( has_filter( 'geodir_output_badge_condition_' . $args['condition'] ) ) {
					$output = apply_filters( 'geodir_output_badge_condition_' . $args['condition'], $output, $find_post, $args, $field );
				}
			} else {
				return $output;
			}
		}

		// If not then we run the standard output.
		if ( empty( $output ) ) {
			$search = $args['search'];

			// Address fields.
			if ( in_array( $match_field, $address_fields ) && ( $address_fields = geodir_post_meta_address_fields( '' ) ) ) {
				if ( ! empty( $address_fields[ $match_field ] ) ) {
					$field = $address_fields[ $match_field ];
				}
			}

			$is_date = ( ! empty( $field['type'] ) && $field['type'] == 'datepicker' ) || in_array( $match_field, array( 'post_date', 'post_modified' ) ) ? true : false;
			/**
			 * @since 2.0.0.81
			 */
			$is_date = apply_filters( 'geodir_post_badge_is_date', $is_date, $match_field, $field, $args, $find_post );

			$match_value = isset($find_post->{$match_field}) ? esc_attr( trim( $find_post->{$match_field} ) ) : ''; // escape user input
			$match_found = $match_field === '' ? true : false;

			if ( ! $match_found ) {
				if ( ( $match_field == 'post_date' || $match_field == 'post_modified' ) && ( empty( $args['condition'] ) || $args['condition'] == 'is_greater_than' || $args['condition'] == 'is_less_than' ) ) {
					if ( strpos( $search, '+' ) === false && strpos( $search, '-' ) === false ) {
						$search = '+' . $search;
					}
					$the_time = $match_field == 'post_modified' ? get_the_modified_date( 'Y-m-d', $find_post ) : get_the_time( 'Y-m-d', $find_post );
					$until_time = strtotime( $the_time . ' ' . $search . ' days' );
					$now_time   = strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) );
					if ( ( empty( $args['condition'] ) || $args['condition'] == 'is_less_than' ) && $until_time > $now_time ) {
						$match_found = true;
					} elseif ( $args['condition'] == 'is_greater_than' && $until_time < $now_time ) {
						$match_found = true;
					}
				}
				else {
					switch ( $args['condition'] ) {
						case 'is_equal':
							$match_found = (bool) ( $search != '' && $match_value == $search );
							break;
						case 'is_not_equal':
							$match_found = (bool) ( $search != '' && $match_value != $search );
							break;
						case 'is_greater_than':
							$match_found = (bool) ( $search != '' && ( is_float( $search ) || is_numeric( $search ) ) && ( is_float( $match_value ) || is_numeric( $match_value ) ) && $match_value > $search );
							break;
						case 'is_less_than':
							$match_found = (bool) ( $search != '' && ( is_float( $search ) || is_numeric( $search ) ) && ( is_float( $match_value ) || is_numeric( $match_value ) ) && $match_value < $search );
							break;
						case 'is_empty':
							$match_found = (bool) ( $match_value === '' || $match_value === false || $match_value === '0' || is_null( $match_value ) );
							break;
						case 'is_not_empty':
							$match_found = (bool) ( $match_value !== '' && $match_value !== false && $match_value !== '0' && ! is_null( $match_value ) );
							break;
						case 'is_contains':
							$match_found = (bool) ( $search != '' && stripos( $match_value, $search ) !== false );
							break;
						case 'is_not_contains':
							$match_found = (bool) ( $search != '' && stripos( $match_value, $search ) === false );
							break;
					}
				}
			}

			/**
			 * @since 2.0.0.67
			 */
			$match_found = apply_filters( 'geodir_post_badge_check_match_found', $match_found, $args, $find_post );

			if ( $match_found ) {
				// check for price format
				if ( isset( $field['data_type'] ) && ( $field['data_type'] == 'INT' || $field['data_type'] == 'FLOAT' || $field['data_type'] == 'DECIMAL' ) && isset( $field['extra_fields'] ) && $field['extra_fields'] ) {
					$extra_fields = stripslashes_deep( maybe_unserialize( $field['extra_fields'] ) );

					if ( ! empty( $extra_fields ) && isset( $extra_fields['is_price'] ) && $extra_fields['is_price'] ) {
						if ( ceil( $match_value ) > 0 ) {
							$match_value = geodir_currency_format_number( $match_value, $field );
						}
					} else if ( isset( $field['data_type'] ) && $field['data_type'] == 'INT' ) {
						if ( ceil( $match_value ) > 0 ) {
							$match_value = geodir_cf_format_number( $match_value, $field );
						}
					} else if ( isset( $field['data_type'] ) && ( $field['data_type'] == 'FLOAT' || $field['data_type'] == 'DECIMAL' ) ) {
						if ( ceil( $match_value ) > 0 ) {
							$match_value = geodir_cf_format_decimal( $match_value, $field );
						}
					}
				}

				if ( $is_date && ! empty( $match_value ) && strpos( $match_value, '0000-00-00' ) === false ) {
					$args['datetime'] = mysql2date( 'c', $match_value, false );
				}

				// Option value
				if ( ! empty( $field['option_values'] ) ) {
					$option_values = geodir_string_values_to_options( stripslashes_deep( $field['option_values'] ), true );

					if ( ! empty( $option_values ) ) {
						if ( ! empty( $field['field_type'] ) && $field['field_type'] == 'multiselect' ) {
							$values = explode( ',', trim( $match_value, ', ' ) );

							if ( is_array( $values ) ) {
								$values = array_map( 'trim', $values );
							}

							$_match_value = array();
							foreach ( $option_values as $option_value ) {
								if ( isset( $option_value['value'] ) && in_array( $option_value['value'], $values ) ) {
									$_match_value[] = $option_value['label'];
								}
							}

							$match_value = ! empty( $_match_value ) ? implode( ', ', $_match_value ) : '';
						} else {
							foreach ( $option_values as $option_value ) {
								if ( isset( $option_value['value'] ) && $option_value['value'] == $match_value ) {
									$match_value = $option_value['label'];
								}
							}
						}
					}
				}

				/**
				 * @since 2.0.0.75
				 */
				$match_value = apply_filters( 'geodir_post_badge_match_value', $match_value, $match_field, $args, $find_post, $field );

				// badge text
				if ( empty( $badge ) && empty( $args['icon_class'] ) ) {
					if ( isset( $field['frontend_title'] ) ) {
						$badge = $field['frontend_title'] ? stripslashes( __( $field['frontend_title'], 'geodirectory' ) ) : '';
					} else if ( $match_field == 'default_category' ) {
						$badge = __( 'Default Category', 'geodirectory' ); // default_category don't have frontend_title.
					}
				}
				if( !empty( $badge ) && $badge = str_replace("%%input%%",$match_value,$badge) ){
					// will be replace in condition check
				}
				if( !empty( $badge ) && $post_id && $badge = str_replace("%%post_url%%",get_permalink($post_id),$badge) ){
					// will be replace in condition check
				}

				//link url, replace vars
				$badge_link = ! empty( $args['link'] ) ? $args['link'] : '';

				// default_category
				if ( ! empty( $find_post->default_category ) ) {
					if ( ! empty( $badge_link ) && $match_field == 'default_category' && strpos( $badge_link, "%%input%%" ) !== false ) {
						$term_link = get_term_link( absint( $find_post->default_category ), $post_type . 'category' );

						if ( ! is_wp_error( $term_link ) ) {
							$badge_link = str_replace( "%%input%%", $term_link, $badge_link );
						}
					}

					// cat_url
					if ( strpos( $badge, "%%cat_url%%" ) !== false || strpos( $badge_link, "%%cat_url%%" ) !== false ) {
						$term_link = get_term_link( absint( $find_post->default_category ), $post_type . 'category' );

						if ( ! is_wp_error( $term_link ) ) {
							$badge = str_replace( "%%cat_url%%", $term_link, $badge );
							$badge_link = str_replace( "%%cat_url%%", $term_link, $badge_link );
						}
					}

					// cat_fa_icon
					if ( strpos( $args['icon_class'], "%%cat_fa_icon%%" ) !== false && ( $cat_fa_icon = get_term_meta( absint( $find_post->default_category ), 'ct_cat_font_icon', true ) ) ) {
						$args['icon_class'] = str_replace( "%%cat_fa_icon%%", esc_attr( $cat_fa_icon ), $args['icon_class'] );
					}
				}

				if ( ! empty( $badge_link ) && ( $badge_link = str_replace( "%%input%%", $match_value, $badge_link ) ) ) {
					// will be replace in condition check
				}

				if ( ! empty( $badge_link ) && $post_id && ( $badge_link = str_replace( "%%post_url%%", get_permalink( $post_id ), $badge_link ) ) ) {
					// will be replace in condition check
				}

				/**
				 * Filters badge link.
				 *
				 * @since 2.2.9
				 *
				 * @param string $link Badge link.
				 * @param string $match_field Match field.
				 * @param array $args The badge parameters.
				 * @param array $find_post Post object.
				 * @param array $field The custom field array.
				 */
				$badge_link = apply_filters( 'geodir_post_badge_link', $badge_link, $match_field, $args, $find_post, $field );

				if ( ! empty( $badge_link ) ) {
					$badge_link = geodir_replace_variables( $badge_link );
				}

				$args['link'] = $badge_link;

				if ( empty( $badge ) ) {
					if ( empty( $badge ) && $match_field == 'post_date' ) {
						$badge = __( 'NEW', 'geodirectory' );
					} elseif ( empty( $badge ) && $match_field == 'post_modified' ) {
						$badge = __( 'UPDATED', 'geodirectory' );
					}
				}

				if ( ! empty( $args['preview'] ) && ! $badge ) {
					$badge = 'Badge';
				}

				// Replace other post variables
				if ( ! empty( $badge ) ) {
					$badge = geodir_replace_variables( $badge );
				}

				if(!empty($args['popover_title'])){
					$args['popover_title'] = geodir_replace_variables($args['popover_title']);
					$args['popover_title'] = str_replace("%%input%%",$match_value,$args['popover_title']);
				}
				if(!empty($args['popover_text'])){
					$args['popover_text'] = geodir_replace_variables($args['popover_text']);
					$args['popover_text'] = str_replace("%%input%%",$match_value,$args['popover_text']);
				}
				if(!empty($args['tooltip_text'])){
					$args['tooltip_text'] = geodir_replace_variables($args['tooltip_text']);
					$args['tooltip_text'] = str_replace("%%input%%",$match_value,$args['tooltip_text']);
				}
				if(!empty($args['hover_content'])){
					$args['hover_content'] = geodir_replace_variables($args['hover_content']);
					$args['hover_content'] = str_replace("%%input%%",$match_value,$args['hover_content']);
				}

				$class = '';
				if(!$design_style){
					if ( ! empty( $args['size'] ) ) {
						$class .= ' gd-badge-' . sanitize_title( $args['size'] );
					}
					if ( ! empty( $args['alignment'] ) ) {
						$class .= ' gd-badge-align' . sanitize_title($args['alignment']);
					}
				}

				if ( ! empty( $args['css_class'] ) ) {
					$class .= ' ' . esc_attr($args['css_class']);
				}

				// new window
				$new_window = '';
				if ( ! empty( $args['new_window'] ) ) {
					$new_window = ' target="_blank" ';
				}

				// check if its external it should be no follow
				$rel = '';
				if(!empty($args['link'])){
					$rel = strpos($args['link'], get_site_url()) !== false ? '' : 'rel="nofollow"';
				}

				// allow gd-lity class to enable lity lightbox on link
				if(!empty($args['link']) && !empty($args['css_class']) &&  strpos($args['css_class'], 'gd-lity') !== false){
					$rel .= ' data-lity ';
				}

				// onclick
				$onclick = '';
				if(!empty($args['onclick'])){
					$onclick = 'onclick="'.esc_attr($args['onclick']).'"';
				}

				// FontAwesome icon
				$icon = '';
				if(!empty($args['icon_class'])){
					$icon = '<i class="'.esc_attr($args['icon_class']).'" ></i>';
				}

				// data-attributes
				$extra_attributes = '';
				if(!empty($args['extra_attributes']) && !is_array($args['extra_attributes'])){
					$extra_attributes = esc_attr( $args['extra_attributes'] );
					$extra_attributes = str_replace("&quot;",'"',$extra_attributes);
				}

				$badge = ! empty( $badge ) ? __( wp_specialchars_decode( $badge, ENT_QUOTES ), 'geodirectory' ) : '';

				// title
				$title = $badge ? $badge : ( ! empty( $field['frontend_title'] ) ? __( $field['frontend_title'], 'geodirectory' ) : '' );
				if ( ! empty( $title ) ) {
					$title = sanitize_text_field( stripslashes( $title ) );
				}

				// Inner tag attributes
				$inner_attributes = '';
				if ( ! empty( $args['datetime'] ) ) {
					$inner_attributes .= 'datetime="' . esc_attr( $args['datetime'] ) . '"';
				}

				// set badge text as secondary if icon is set.
				if( $icon ){
					$badge = " <span class='gd-secondary gv-secondary'>$badge</span>";
				}

				// phone & email link
				if ( ! empty( $field ) && ! empty( $field['field_type'] ) && ! empty( $args['link'] ) && strpos( $args['link'], 'http' ) !== 0 ) {
					if ( $field['field_type'] == 'phone' ) {
						$rel = 'rel="nofollow"';
						if ( strpos( $args['link'], 'tel:' ) !== 0 ) {
							$args['link'] = 'tel:' . preg_replace( '/[^0-9+]/', '', $args['link'] );
						}
					} elseif ( $field['field_type'] == 'email' ) {
						$rel = 'rel="nofollow"';
						if ( strpos( $args['link'], 'mailto:' ) !== 0 ) {
							$args['link'] = 'mailto:' . sanitize_email( $args['link'] );
						}
					}
				}

				/**
				 * @since 2.0.0.68
				 */
				$badge = apply_filters( 'geodir_post_badge_output_badge', $badge, $match_value, $match_field, $args, $find_post, $field );

				if ( ! empty( $badge ) && ( $match_field == 'street' || in_array( $match_field, $address_fields ) ) ) {
					$badge = geodir_post_address( $badge, 'post_badge_street', $find_post );
				}

				if ( $design_style ) {
					$bs_prefix = $aui_bs5 ? 'bs-' : '';

					$btn_class = ' gd-badge';
					// color
					$color_custom = true;
					$badge_color = '';
					if ( ! empty( $args['color'] ) ) {
						$badge_color = sanitize_html_class( $args['color'] );
						$color_custom = false;
					}

					// shadow
					if( !empty( $args['shadow'] ) ) {
						if($args['shadow']=='small'){ $btn_class .= ' shadow-sm'; }
						elseif($args['shadow']=='medium'){ $btn_class .= ' shadow'; }
						elseif($args['shadow']=='large'){ $btn_class .= ' shadow-lg'; }
					}

					// type
					if ( ! empty( $args['type'] ) && $args['type'] == 'pill' ) {
						$btn_class .= ' border-0 badge ' . ( $aui_bs5 ? 'rounded-pill' : 'badge-pill' );
						$btn_class .= $badge_color ? ' ' . ( $aui_bs5 ? 'bg-' : 'badge-' ) . $badge_color . ' ' : '';
					} else if ( ! empty( $args['type'] ) && $args['type'] == 'button' ) {
						$btn_class .= ' btn ';
						$btn_class .= $badge_color ? ' btn-' . $badge_color . ' ' : '';
					} else if ( ! empty( $args['type'] ) && $args['type'] == 'link' ) {
						$btn_class .= ' btn btn-link ';
					} else {
						$btn_class .= ' border-0 badge';
						$btn_class .= $badge_color ? ' ' . ( $aui_bs5 ? 'bg-' : 'badge-' ) . $badge_color . ' ' : '';
					}

					//alignment
					if($args['alignment']=='block'){$btn_class .= " d-block ";}
					elseif($args['alignment']=='left'){$btn_class .= ( $aui_bs5 ? ' float-start ms-2 ' : ' float-left mr-2 ' );}
					elseif($args['alignment']=='right'){$btn_class .= ( $aui_bs5 ? ' float-end me-2 ' : ' float-right ml-2 ' );}
					elseif($args['alignment']=='center'){$btn_class .= " mw-100 d-block mx-auto ";}

					if ( ! empty( $args['css_class'] ) ) {
						// replace some old classes
						$user_classes = str_replace(array("gd-ab-","gd-badge-shadow"),array("ab-","shadow"),esc_attr($args['css_class']));
						$btn_class .= ' ' .$user_classes ;
					}

					// Set CSS class for AUI modal
					if ( ! empty( $args['link'] ) && strpos( $btn_class, 'gd-lity' ) !== false && strpos( $btn_class, 'aui-has-embed' ) === false ) {
						$btn_class .= ' aui-has-embed';
					}

					$btn_args = array(
						'data-id' => $post_id,
						'class' => normalize_whitespace( $btn_class ),
						'content' => $badge,
						'style' => $color_custom ? 'background-color:' . esc_attr( $args['bg_color'] ) . ';color:' . esc_attr( $args['txt_color'] ) . ';' : '',
						'data-badge' => esc_attr($match_field),
						'data-badge-condition' => esc_attr($args['condition']),
					);

					// extra attributes
					if(!empty($args['extra_attributes'])){
						$btn_args['extra_attributes'] = $args['extra_attributes'];
					}

						// onclick
					if(!empty($args['onclick'])){
						$btn_args['onclick'] = esc_attr($args['onclick']);
					}

					// AUI modal
					if ( ! empty( $args['link'] ) && strpos( $btn_class, 'aui-has-embed' ) !== false ) {
						$embed_url = geodir_parse_embed_url( $args['link'], array( 'post' => $find_post, 'args' => $args ) );

						if ( $embed_url ) {
							$btn_args['data-embed-url'] = esc_url( $embed_url );
							$btn_args['data-aui-embed'] = 'iframe';
							if ( empty( $args['popover_title'] ) && empty( $args['popover_text'] ) && empty( $args['tooltip_text'] ) ) {
								$args['tooltip_text'] = __( 'Watch Video', 'geodirectory' );
							}
						}
					}

					// CTA
					if( $args['cta'] != '0' ){
						$action = $args['cta'] == '' ? esc_attr($args['key']) : esc_attr($args['cta']);
						$cta = " if(typeof ga == 'function' && !jQuery(this).hasClass('gd-event-tracked')) { ga('send', 'event', {eventCategory: 'CTA',eventAction: '$action',transport: 'beacon' });jQuery(this).addClass('gd-event-tracked');} ";
						if(!empty($btn_args['onclick'])){
							$btn_args['onclick'] .= $cta;
						}else{
							$btn_args['onclick'] = $cta;
						}
					}

					// popover / tooltip
					$pop_link = false;

					if ( ! empty( $args['popover_title'] ) || ! empty( $args['popover_text'] ) ) {
						$btn_args['type'] = "button";
						$btn_args['data-' . $bs_prefix . 'toggle'] = "popover-html";
						$btn_args['data-' . $bs_prefix . 'placement'] = "top";
						$pop_link = true;

						if ( ! empty( $args['popover_title'] ) ) {
							$btn_args['title'] = ! empty( $args['link'] ) && $args['link'] != '#'  ? "<a href='" . esc_url( $args['link'] ) . "' $new_window $rel>" . $args['popover_title'] . "</a>" : $args['popover_title'];
						}

						if ( ! empty( $args['popover_text'] ) ) {
							$btn_args['data-' . $bs_prefix . 'content'] = ! empty( $args['link'] ) && $args['link'] != '#' ? "<a href='" . esc_url( $args['link'] ) . "' $new_window $rel>" . $args['popover_text'] . "</a>" : $args['popover_text'];
						}
					} else if ( ! empty( $args['tooltip_text'] ) ) {
						$btn_args['data-' . $bs_prefix . 'toggle'] = "tooltip";
						$btn_args['data-' . $bs_prefix . 'placement'] = "top";
						$btn_args['title'] = esc_attr( $args['tooltip_text'] );
					}

					// hover content
					if(!empty($args['hover_content'])){
						$btn_args['hover_content'] = $args['hover_content'];
					}
					if(!empty($args['hover_icon'])){
						$btn_args['hover_icon'] = $args['hover_icon'];
					}

					// style
					$btn_args['style'] = '';
					if($color_custom && !empty($args['bg_color'])){
						$btn_args['style'] .= 'background-color:' . esc_attr( $args['bg_color'] ) . ';border-color:' . esc_attr( $args['bg_color'] ).';';
					}
					if($color_custom && !empty($args['txt_color'])){
						$btn_args['style'] .= 'color:' . esc_attr( $args['txt_color'] ) . ';';
					}

					if(!empty($args['link']) && $args['link']!='#' && !$pop_link){
						$btn_args['href'] = $args['link'];
					}

					if(!empty($args['link']) && $new_window){
						$btn_args['new_window'] = true;
					}
					if(!empty($args['icon_class'])) { $btn_args['icon'] = $args['icon_class'];}

					$output = '<span class="bsui gd-badge-meta">';
					if(!empty($args['size'])){$output .= '<span class="'.esc_attr($args['size']).'">';}
					$output .= aui()->badge( $btn_args );
					if(!empty($args['size'])){$output .= '</span>';}
					$output .= '</span>';


				}else{
					$post_id = isset( $find_post->ID ) ? absint( $find_post->ID ) : '';
					$link = ! empty( $args['link'] ) ? ( $args['link'] == 'javascript:void(0);' ? $args['link'] : esc_url( $args['link'] ) ) : '';
					// Element tag
					if ( empty( $args['tag'] ) && $is_date ) {
						$tag = 'time';
					} else {
						$tag = 'div';
					}

					$output = '<div class="gd-badge-meta ' . normalize_whitespace( $class ) . ' gd-badge-meta-' . sanitize_title_with_dashes( esc_attr( $title ) ).'" '.$onclick.' '.$extra_attributes.' title="'.esc_attr( $title ).'">';
					if ( ! empty( $link ) ) {
						$output .= "<a href='" . $link . "' $new_window $rel>";
					}
					// we escape the user input from $match_value but we don't escape the user badge input so they can use html like font awesome.
					$output .= '<' . $tag . ' data-id="' . $post_id . '" class="gd-badge" data-badge="' . esc_attr($match_field) . '" data-badge-condition="' . esc_attr($args['condition']) . '" style="background-color:' . esc_attr( $args['bg_color'] ) . ';color:' . esc_attr( $args['txt_color'] ) . ';" ' . $inner_attributes . '>' . $icon . $badge . '</' . $tag . '>';
					if ( ! empty( $link ) ) {
						$output .= "</a>";
					}
					$output .= '</div>';
				}
			}
		}
	}

	return $output;
}

/**
 * Filters the JOIN clause in the SQL for an adjacent post query.
 *
 * @since 1.6.16
 * @package GeoDirectory
 *
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $join The JOIN clause in the SQL.
 * @param bool $in_same_term Whether post should be in a same taxonomy term.
 * @param array $excluded_terms Array of excluded term IDs.
 * @param string $taxonomy Taxonomy. Used to identify the term used when `$in_same_term` is true.
 * @param WP_Post $post WP_Post object.
 *
 * @return string Filtered SQL JOIN clause.
 */
function geodir_previous_next_post_join( $join, $in_same_term, $excluded_terms, $taxonomy, $post ) {
	global $plugin_prefix;

	if ( ! empty( $post->post_type ) && in_array( $post->post_type, geodir_get_posttypes() ) ) {
		$join .= " INNER JOIN " . $plugin_prefix . $post->post_type . "_detail AS gd ON gd.post_id = p.ID";
	}

	return $join;
}

add_filter( 'get_previous_post_join', 'geodir_previous_next_post_join', 10, 5 );
add_filter( 'get_next_post_join', 'geodir_previous_next_post_join', 10, 5 );

/**
 * Filters the WHERE clause in the SQL for an adjacent post query.
 *
 * @since 1.6.16
 * @package GeoDirectory
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $where The `WHERE` clause in the SQL.
 * @param bool $in_same_term Whether post should be in a same taxonomy term.
 * @param array $excluded_terms Array of excluded term IDs.
 * @param string $taxonomy Taxonomy. Used to identify the term used when `$in_same_term` is true.
 * @param WP_Post $post WP_Post object.
 *
 * @return string Filtered SQL WHERE clause.
 */
function geodir_previous_next_post_where( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) {
	global $wpdb, $plugin_prefix, $gd_post;

	if ( ! empty( $post->post_type ) && ( ! empty( $gd_post->country ) || ! empty( $gd_post->region ) || ! empty( $gd_post->city ) ) && in_array( $post->post_type, geodir_get_posttypes() ) ) {
		$post_locations     = '';
		$post_locations_var = array();

		if ( ! empty( $gd_post->country ) ) {
			$post_locations .= " AND gd.country = %s";
			$post_locations_var[] = esc_attr( $gd_post->country );
		}

		if ( ! empty( $gd_post->region ) ) {
			$post_locations .= " AND gd.region = %s";
			$post_locations_var[] = esc_attr( $gd_post->region );
		}

		if ( ! empty( $gd_post->city ) ) {
			$post_locations .= " AND gd.city = %s";
			$post_locations_var[] = esc_attr( $gd_post->city );
		}

		$where .= $wpdb->prepare( $post_locations, $post_locations_var );
	}

	return $where;
}

add_filter( 'get_previous_post_where', 'geodir_previous_next_post_where', 10, 5 );
add_filter( 'get_next_post_where', 'geodir_previous_next_post_where', 10, 5 );

/**
 * Returns package information as an objects.
 *
 * @since   2.0.0
 * @package GeoDirectory
 * @deprecated
 *
 * @param object|string $post The post object.
 * @param string $post_type   The post type.
 *
 * @return object Returns filtered package info as an object.
 */
function geodir_get_post_package_id( $post = '', $post_type = '' ) {
	$package = geodir_get_post_package( $post, $post_type );

	$package_id = ! empty( $package ) && ! empty( $package->id ) ? $package->id : 0;

	return $package_id;
}

/**
 * Returns package information as an objects.
 *
 * @since   2.0.0
 * @package GeoDirectory
 * @deprecated
 *
 * @param object|string $post The post object.
 * @param string $post_type   The post type.
 *
 * @return object Returns filtered package info as an object.
 */
function geodir_get_post_package( $post = '', $post_type = '' ) {
	$package = array(
		'id' => 0,
	);

	return (object)apply_filters( 'geodir_get_post_package', (object)$package, $post, $post_type );
}

/**
 * A list of fields that should not be auto replaced.
 *
 * @return array
 */
function geodir_get_no_replace_fields(){
	return array(
		'post_password',
		'submit_ip'
	);
}

/**
 * Replace custom variables in text.
 *
 * @param $text
 * @param string $post_id
 *
 * @return mixed
 */
function geodir_replace_variables( $text, $post_id = '' ) {
	global $gd_post;

	// Only run if we have a GD post and the start of a var
	if ( ! empty( $gd_post->ID ) && strpos( $text, '%%' ) !== false ) {
		$non_replace = geodir_get_no_replace_fields();

		foreach( $gd_post as $key => $val ) {
			if ( ! in_array( $key, $non_replace ) ) {
				// Replace plain variables.
				if ( strpos( $text, '%%' . $key . '%%' ) !== false ) {
					$val = apply_filters( 'geodir_replace_variables_' . $key, $val, $text );
					$text = str_replace( '%%' . $key . '%%', $val, $text );
				}

				// Replace encoded variables.
				if ( strpos( $text, '%%' . $key . '_encode%%' ) !== false ) {
					$encode_val = ! empty( $val ) ? urlencode( trim( $val ) ) : '';
					$encode_val = apply_filters( 'geodir_replace_variables_encode_' . $key, $encode_val, $text );
					$text = str_replace( '%%' . $key . '_encode%%', $encode_val, $text );
				}
			}
		}
	}

	return $text;
}

/**
 * Filter post badge match value.
 *
 * @since 2.0.0.67
 *
 * @param bool $match_found True if match found else False.
 * @param array $args Badge arguments.
 * @param object $gd_post The GD post object.
 * @return bool
 */
function geodir_post_badge_filter_match_found( $match_found, $args, $gd_post ) {
	$match_field = $args['key'];

	if ( $match_field == 'post_category' || $match_field == 'post_tags' ) {
		$search = $args['search'];
		if ( $search !== '' ) {
			$search = array_map( 'trim', explode( ',', stripslashes( $search ) ) );
			$search = array_filter( array_unique( $search ) );
		}

		$value = isset( $gd_post->{$match_field} ) ? $gd_post->{$match_field} : '';
		if ( $value !== '' ) {
			$value = array_map( 'trim', explode( ',', stripslashes( $value ) ) );
			$value = array_filter( array_unique( $value ) );
		}

		if ( $args['condition'] == 'is_contains' ) {
			$match_found = false;

			if ( ! empty( $search ) && ! empty( $value ) ) {
				foreach ( $search as $_search ) {
					if ( in_array( $_search, $value ) ) {
						$match_found = true; // Contains any value
						break;
					}
				}
			}
		} elseif ( $args['condition'] == 'is_not_contains' ) {
			$match_found = false;

			if ( ! empty( $search ) && ! empty( $value ) ) {
				$matches = 0;
				foreach ( $search as $_search ) {
					if ( ! in_array( $_search, $value ) ) {
						$matches++; // Not contains all value
					}
				}

				if ( $matches == count( $search ) ) {
					$match_found = true;
				}
			}
		}
	}

	return $match_found;
}
add_filter( 'geodir_post_badge_check_match_found', 'geodir_post_badge_filter_match_found', 10, 3 );

/**
 * Sanitize text value.
 *
 * @since 2.0.0.67
 *
 * @param string $value Field value.
 * @param object $gd_post GeoDirectory post object.
 * @param object $custom_field Custom field.
 * @param int $post_id Post id.
 * @param object $post Post.
 * @param string $update Update.
 * @return string $value Sanitize business hours.
 */
function geodir_validate_custom_field_value_text( $value, $gd_post, $custom_field, $post_id, $post, $update ) {
	if ( $value != '' ) {
		if ( is_array( $value ) ) {
			$value = array_map( 'geodir_clean', $value );
		} else {
			$value = is_scalar( $value ) ? geodir_clean( stripslashes( $value ) ) : $value;
		}
	}
	return $value;
}
add_filter( 'geodir_custom_field_value_checkbox', 'geodir_validate_custom_field_value_text', 10, 6 );
add_filter( 'geodir_custom_field_value_datepicker', 'geodir_validate_custom_field_value_text', 10, 6 );
add_filter( 'geodir_custom_field_value_email', 'geodir_validate_custom_field_value_text', 10, 6 );
add_filter( 'geodir_custom_field_value_multiselect', 'geodir_validate_custom_field_value_text', 10, 6 );
add_filter( 'geodir_custom_field_value_phone', 'geodir_validate_custom_field_value_text', 10, 6 );
add_filter( 'geodir_custom_field_value_radio', 'geodir_validate_custom_field_value_text', 10, 6 );
add_filter( 'geodir_custom_field_value_select', 'geodir_validate_custom_field_value_text', 10, 6 );
add_filter( 'geodir_custom_field_value_text', 'geodir_validate_custom_field_value_text', 10, 6 );

/**
 * Sanitize url value.
 *
 * @since 2.3.99
 *
 * @param string $value Field value.
 * @param object $gd_post GeoDirectory post object.
 * @param object $custom_field Custom field.
 * @param int $post_id Post id.
 * @param object $post Post.
 * @param string $update Update.
 * @return string $value Sanitized url.
 */
function geodir_validate_custom_field_value_url( $value, $gd_post, $custom_field, $post_id, $post, $update ) {
	if ( $value != '' ) {
		if ( is_array( $value ) ) {
			$value = array_map( 'sanitize_url', $value );
		} else {
			$value = is_scalar( $value ) ? sanitize_url( wp_unslash( $value ) ) : $value;
		}
	}

	return $value;
}
add_filter( 'geodir_custom_field_value_url', 'geodir_validate_custom_field_value_url', 10, 6 );

/**
 * Sanitize textarea/html value.
 *
 * @since 2.0.0.67
 *
 * @param string $value Field value.
 * @param object $gd_post GeoDirectory post object.
 * @param object $custom_field Custom field.
 * @param int $post_id Post id.
 * @param object $post Post.
 * @param string $update Update.
 * @return string $value Sanitize business hours.
 */
function geodir_validate_custom_field_value_textarea( $value, $gd_post, $custom_field, $post_id, $post, $update ) {
	if ( $value != '' ) {
		$html = false;
		// Post content & video contains html/embed code.
		if ( $custom_field->field_type == 'html' || $custom_field->htmlvar_name == 'post_content' || $custom_field->htmlvar_name == 'video' ) {
			$html = true;
		} else {
			// Check if textarea field has html/embed enabled.
			$extra_fields = ! empty( $custom_field->extra_fields ) ? stripslashes_deep( maybe_unserialize( $custom_field->extra_fields ) ) : NULL;
			if ( is_array( $extra_fields ) && ( ! empty( $extra_fields['advanced_editor'] ) || ! empty( $extra_fields['embed'] ) )  ) {
				$html = true;
			}
		}

		if ( $html ) {
			$allowed_html = wp_kses_allowed_html( 'post' );

			if ( is_array( $allowed_html ) ) {
				// <iframe>
				if ( ! isset( $allowed_html['iframe'] ) ) {
					$allowed_html['iframe']     = array(
						'class'        => true,
						'id'           => true,
						'src'          => true,
						'width'        => true,
						'height'       => true,
						'frameborder'  => true,
						'marginwidth'  => true,
						'marginheight' => true,
						'scrolling'    => true,
						'style'        => true,
						'title'        => true,
						'allow'        => true,
						'allowfullscreen' => true,
						'data-*'       => true,
					);
				}
			}

			/**
			 * Filters the HTML that is allowed for a given field.
			 *
			 * @since 2.0.0.68
			 *
			 * @param array[]|string $allowed_html Allowed html tags.
			 * @param object $custom_field Custom field.
			 * @param string $value Field value.
			 * @param object $gd_post GeoDirectory post object.
			 * @param string $context_type Context name.
			 */
			$allowed_html = apply_filters( 'geodir_custom_field_kses_allowed_html', $allowed_html, $custom_field, $value, $gd_post );

			if ( is_array( $value ) ) {
				$value = array_map( function( $value ) use ( $allowed_html ) {
					return geodir_sanitize_html_field( $value, $allowed_html );
				}, $value );
			} else {
				$value = is_scalar( $value ) ? geodir_sanitize_html_field( $value, $allowed_html ) : $value;
			}
		} else {
			if ( is_array( $value ) ) {
				$value = array_map( 'geodir_sanitize_textarea_field', $value );
			} else {
				$value = is_scalar( $value ) ? geodir_sanitize_textarea_field( $value ) : $value;
			}
		}

		// post_content saved early, so don't need sanitize.
		if ( ! empty( $value ) && $custom_field->htmlvar_name != 'post_content' ) {
			/** This filter is documented in includes/class-geodir-post-data.php */
			$value = apply_filters( 'geodir_extra_sanitize_textarea_field', $value, array( 'default' => $value, 'field_key' => $custom_field->htmlvar_name, 'gd_post' => $gd_post, 'allow_html' => $html ) );
		}
	}

	return $value;
}
add_filter( 'geodir_custom_field_value_html', 'geodir_validate_custom_field_value_textarea', 10, 6 );
add_filter( 'geodir_custom_field_value_textarea', 'geodir_validate_custom_field_value_textarea', 10, 6 );

/**
 * Get the post meta advance custom fields.
 *
 * @since 2.0.0.86
 *
 * $param string $post_type The post type.
 * @return array Standard fields.
 */
function geodir_post_meta_advance_fields( $post_type = 'gd_place' ) {
	$fields = array();

	// Standard fields
	$standard_fields = geodir_post_meta_standard_fields( $post_type );
	if ( ! empty( $standard_fields ) ) {
		$fields = $standard_fields;
	}

	// Address fields
	$address_fields = geodir_post_meta_address_fields( $post_type );
	if ( ! empty( $address_fields ) ) {
		$fields = ! empty( $fields ) ? array_merge( $fields, $address_fields ) : $address_fields;
	}

	/**
	 * Filter the post meta advance fields.
	 *
	 * @since 2.0.0.86
	 */
	return apply_filters( 'geodir_post_meta_advance_fields', $fields, $post_type );
}

/**
 * Get the post meta standard fields.
 *
 * @since 2.0.0.86
 *
 * $param string $post_type The post type.
 * @return array Standard fields.
 */
function geodir_post_meta_standard_fields( $post_type = 'gd_place' ) {
	$fields = array();

	$fields['default_category'] = array(
		'type' => 'custom',
		'name' => 'default_category',
		'htmlvar_name' => 'default_category',
		'frontend_title' => __( 'Default Category', 'geodirectory' ),
		'field_icon' => 'fas fa-folder-open',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['overall_rating'] = array(
		'type' => 'custom',
		'name' => 'overall_rating',
		'htmlvar_name' => 'overall_rating',
		'frontend_title' => __( 'Overall Rating', 'geodirectory' ),
		'field_icon' => 'fas fa-star',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['rating_count'] = array(
		'type' => 'custom',
		'name' => 'rating_count',
		'htmlvar_name' => 'rating_count',
		'frontend_title' => __( 'Rating Count', 'geodirectory' ),
		'field_icon' => 'fas fa-comments',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['post_type'] = array(
		'type' => 'custom',
		'name' => 'post_type',
		'htmlvar_name' => 'post_type',
		'frontend_title' => __( 'Post Type', 'geodirectory' ),
		'field_icon' => 'fas fa-list',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['post_date'] = array(
		'name' => 'post_date',
		'htmlvar_name' => 'post_date',
		'frontend_title' => __('Published','geodirectory'),
		'type' => 'datepicker',
		'field_icon' => 'fas fa-calendar-alt',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => array( 'date_format' => geodir_date_format() ),
	);

	$fields['post_date_gmt'] = array(
		'name' => 'post_date_gmt',
		'htmlvar_name' => 'post_date_gmt',
		'frontend_title' => __('Published','geodirectory'),
		'type' => 'datepicker',
		'field_icon' => 'fas fa-calendar-alt',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => array( 'date_format' => geodir_date_format() ),
	);

	$fields['post_modified'] = array(
		'name' => 'post_modified',
		'htmlvar_name' => 'post_modified',
		'frontend_title' => __('Modified','geodirectory'),
		'type' => 'datepicker',
		'field_icon' => 'fas fa-calendar-alt',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => array( 'date_format' => geodir_date_format() ),
	);

	$fields['post_modified_gmt'] = array(
		'name' => 'post_modified_gmt',
		'htmlvar_name' => 'post_modified_gmt',
		'frontend_title' => __('Modified','geodirectory'),
		'type' => 'datepicker',
		'field_icon' => 'fas fa-calendar-alt',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => array( 'date_format' => geodir_date_format() ),
	);

	$fields['post_author'] = array(
		'name' => 'post_author',
		'htmlvar_name' => 'post_author',
		'frontend_title' => __('Author','geodirectory'),
		'type' => 'author',
		'field_icon' => 'fas fa-user',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => '',
	);

	$fields['post_status'] = array(
		'type' => 'custom',
		'name' => 'post_status',
		'htmlvar_name' => 'post_status',
		'frontend_title' => __( 'Status', 'geodirectory' ),
		'field_icon' => 'fas fa-play',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['post_id'] = array(
		'type' => 'custom',
		'name' => 'post_id',
		'htmlvar_name' => 'post_id',
		'frontend_title' => __( 'ID', 'geodirectory' ),
		'field_icon' => 'fas fa-thumbtack',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['post_link'] = array(
		'type' => 'custom',
		'name' => 'post_link',
		'htmlvar_name' => 'post_link',
		'frontend_title' => __( 'Post Link', 'geodirectory' ),
		'field_icon' => 'fas fa-link',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	/**
	 * Filter the post meta standard fields info.
	 *
	 * @since 2.0.0.49
	 */
	return apply_filters( 'geodir_post_meta_standard_fields', $fields, $post_type );
}

/**
 * Get the post meta address fields.
 *
 * @since 2.0.0.86
 *
 * @global array $geodir_post_meta_fields Cached fields.
 *
 * $param string $post_type The post type.
 * @return array Address fields.
 */
function geodir_post_meta_address_fields( $post_type = 'gd_place' ) {
	global $geodir_post_meta_fields;

	if ( empty( $post_type ) ) {
		$post_type = 'gd_place';
	} else if ( ! GeoDir_Post_types::supports( $post_type, 'location' ) ) {
		return array();
	}

	if ( empty( $geodir_post_meta_fields ) ) {
		$geodir_post_meta_fields = array();
	}

	// Return cached fields.
	if ( ! empty( $geodir_post_meta_fields[ $post_type ] ) ) {
		return $geodir_post_meta_fields[ $post_type ];
	}

	$field = geodir_get_field_infoby( 'htmlvar_name', 'address', $post_type, false );
	$extra_fields = ! empty( $field['extra_fields'] ) ? stripslashes_deep( maybe_unserialize( $field['extra_fields'] ) ) : NULL;
	$field_icon = ! empty( $field['field_icon'] ) ? $field['field_icon'] : 'fas fa-map-marker-alt';

	$fields = array();

	$fields['street'] = array(
		'type' => 'custom',
		'name' => 'street',
		'htmlvar_name' => 'street',
		'frontend_title' => ( ! empty( $field['frontend_title'] ) ? __( $field['frontend_title'], 'geodirectory' ) : __( 'Address', 'geodirectory' ) ),
		'field_icon' => $field_icon,
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['street2'] = array(
		'type' => 'custom',
		'name' => 'street2',
		'htmlvar_name' => 'street2',
		'frontend_title' => __( 'Address line 2', 'geodirectory' ),
		'field_icon' => $field_icon,
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['city'] = array(
		'type' => 'custom',
		'name' => 'city',
		'htmlvar_name' => 'city',
		'frontend_title' => ( ! empty( $extra_fields['city_lable'] ) ? __( $extra_fields['city_lable'], 'geodirectory' ) : __( 'City', 'geodirectory' ) ),
		'field_icon' => $field_icon,
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['region'] = array(
		'type' => 'custom',
		'name' => 'region',
		'htmlvar_name' => 'region',
		'frontend_title' => ( ! empty( $extra_fields['region_lable'] ) ? __( $extra_fields['region_lable'], 'geodirectory' ) : __( 'Region', 'geodirectory' ) ),
		'field_icon' => $field_icon,
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['country'] = array(
		'type' => 'custom',
		'name' => 'country',
		'htmlvar_name' => 'country',
		'frontend_title' => ( ! empty( $extra_fields['country_lable'] ) ? __( $extra_fields['country_lable'], 'geodirectory' ) : __( 'Country', 'geodirectory' ) ),
		'field_icon' => $field_icon,
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['zip'] = array(
		'type' => 'custom',
		'name' => 'zip',
		'htmlvar_name' => 'zip',
		'frontend_title' => ( ! empty( $extra_fields['zip_lable'] ) ? __( $extra_fields['zip_lable'], 'geodirectory' ) : __( 'Zip/Post Code', 'geodirectory' ) ),
		'field_icon' => $field_icon,
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['latitude'] = array(
		'type' => 'custom',
		'name' => 'latitude',
		'htmlvar_name' => 'latitude',
		'frontend_title' => __( 'Latitude', 'geodirectory' ),
		'field_icon' => $field_icon,
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['longitude'] = array(
		'type' => 'custom',
		'name' => 'longitude',
		'htmlvar_name' => 'longitude',
		'frontend_title' => __( 'Longitude', 'geodirectory' ),
		'field_icon' => $field_icon,
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields['map_directions'] = array(
		'type' => 'custom',
		'name' => 'map_directions',
		'htmlvar_name' => 'map_directions',
		'frontend_title' => __( 'Map Directions', 'geodirectory' ),
		'field_icon' => 'fas fa-directions',
		'field_type_key' => '',
		'css_class' => '',
		'extra_fields' => ''
	);

	$fields = apply_filters( 'geodir_post_meta_address_fields', $fields, $post_type );

	$geodir_post_meta_fields[ $post_type ] = $fields;

	return $fields;
}
