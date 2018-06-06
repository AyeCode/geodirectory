<?php
/**
 * Custom functions
 *
 * @since   1.0.0
 * @package GeoDirectory
 */




/**
 * Returns package information as an objects.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param array $package_info Package info array.
 * @param object|string $post The post object.
 * @param string $post_type   The post type.
 *
 * @return object Returns filtered package info as an object.
 */
function geodir_post_package_info( $package_info, $post = '', $post_type = '' ) {
	$package_info['pid']              = 0;
	$package_info['days']             = 0;
	$package_info['amount']           = 0;
	$package_info['featured']         = 0;
	$package_info['image_limit']      = '';

	/**
	 * Filter listing package info.
	 *
	 * @since 1.0.0
	 *
	 * @param array $package_info  {
	 *                             Attributes of the package_info.
	 *
	 * @type int $pid              Package ID. Default 0.
	 * @type int $days             Package validity in Days. Default 0.
	 * @type int $amount           Package amount. Default 0.
	 * @type int $featured      Is this featured package? Default 0.
	 * @type string $image_limit   Image limit for this package. Default "".
	 * @type int $google_analytics Add analytics to this package. Default 1.
	 *
	 * }
	 * @param object|string $post  The post object.
	 * @param string $post_type    The post type.
	 */
	return (object) apply_filters( 'geodir_post_package_info', $package_info, $post, $post_type );

}

/**
 * Send enquiry to listing author
 *
 * This function let the user to send Enquiry to listing author. If listing author email not available, then admin
 * email will be used. Email content will be used WP Admin -> Geodirectory -> Notifications -> Other Emails -> Email
 * enquiry
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb    WordPress Database object.
 *
 * @param array $request   {
 *                         The submitted form fields as an array.
 *
 * @type string $sendact   Enquiry type. Default "send_inqury".
 * @type string $pid       Post ID.
 * @type string $inq_name  Sender name.
 * @type string $inq_email Sender mail.
 * @type string $inq_phone Sender phone.
 * @type string $inq_msg   Email message.
 *
 * }
 */
function geodir_send_inquiry( $request ) {
	// strip slashes from text
	if ( ! GeoDir_Email::is_email_enabled( 'send_enquiry' ) ) {
		return false;
	}

	$request = ! empty( $request ) ? stripslashes_deep( $request ) : $request;

	$post_id = ! empty( $request['pid'] ) ? (int)$request['pid'] : 0;
	if ( ! $post_id ) {
		return false;
	}

	$gd_post = geodir_get_post_info( $post_id );
	if ( empty( $gd_post ) ) {
		return false;
	}

	$data = $request;
	$data['post_id'] = $gd_post->ID;
	$data['from_name'] = ! empty( $request['inq_name'] ) ? $request['inq_name'] : '';
	$data['from_email'] = ! empty( $request['inq_email'] ) ? $request['inq_email'] : '';
	$data['phone'] = ! empty( $request['inq_phone'] ) ? $request['inq_phone'] : '';
	$data['comments'] = ! empty( $request['inq_msg'] ) ? $request['inq_msg'] : '';

	$allow = apply_filters( 'geodir_allow_send_enquiry_email', true, $gd_post, $data );
	if ( ! $allow ) {
		return false;
	}
	
	/**
	 * Send enquiry email.
	 *
	 * @since 1.0.0
	 *
	 * @param object $gd_post The post object.
	 * @param array $data {
	 *     The submitted form fields as an array.
	 *
	 *     @type string $sendact    Enquiry type. Default "send_inqury".
	 *     @type string $pid        Post ID.
	 *     @type string $from_name  Sender name.
	 *     @type string $from_email Sender mail.
	 *     @type string $phone 	    Sender phone.
	 *     @type string $comments   Email message.
	 *
	 * }
	 */
	do_action( 'geodir_send_enquiry_email', $gd_post, $data );
	
	$redirect_to = add_query_arg( array( 'send_inquiry' => 'success' ), get_permalink( $post_id ) );

	/**
	 * Filter redirect url after the send enquiry email is sent.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url Redirect url.
	 */
	$redirect_to = apply_filters( 'geodir_send_enquiry_after_submit_redirect', $redirect_to );
	wp_redirect( $redirect_to );
	geodir_die();
}

/**
 * Adds open div before the tab content.
 *
 * This function adds open div before the tab content like post information, post images, reviews etc.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $hash_key
 */
function geodir_before_tab_content( $hash_key ) {
	switch ( $hash_key ) {
		case 'post_info' :
			echo '<div class="geodir-company_info field-group">';
			break;
		case 'post_images' :
			/**
			 * Filter post gallery HTML id.
			 *
			 * @since 1.0.0
			 */
			echo ' <div id="' . apply_filters( 'geodir_post_gallery_id', 'geodir-post-gallery' ) . '" class="clearfix" >';
			break;
		case 'reviews' :
			echo '<div id="reviews-wrap" class="clearfix"> ';
			break;
		case 'post_video':
			echo ' <div id="post_video-wrap" class="clearfix">';
			break;
		case 'special_offers':
			echo '<div id="special_offers-wrap" class="clearfix">';
			break;
	}
}

/**
 * Adds closing div after the tab content.
 *
 * This function adds closing div after the tab content like post information, post images, reviews etc.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $hash_key
 */
function geodir_after_tab_content( $hash_key ) {
	switch ( $hash_key ) {
		case 'post_info' :
			echo '</div>';
			break;
		case 'post_images' :
			echo '</div>';
			break;
		case 'reviews' :
			echo '</div>';
			break;
		case 'post_video':
			echo '</div>';
			break;
		case 'special_offers':
			echo '</div>';
			break;
	}
}





/**
 * Removes the section title
 *
 * Removes the section title "Posts sort options", if the custom field type is multiselect or textarea or taxonomy.
 * Can be found here. WP Admin -> Geodirectory -> Place settings -> Custom fields
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $title      The section title.
 * @param string $field_type The field type.
 *
 * @return string Returns the section title.
 */
function geodir_advance_customfields_heading( $title, $field_type ) {

	if ( in_array( $field_type, array( 'multiselect', 'textarea', 'taxonomy' ) ) ) {
		$title = '';
	}

	return $title;
}




//add_action('wp_footer', 'geodir_category_count_script', 10);
/**
 * Adds the category post count javascript code
 *
 * @since       1.0.0
 * @package     GeoDirectory
 * @global string $geodir_post_category_str The geodirectory post category.
 * @depreciated No longer needed.
 */
function geodir_category_count_script() {
	global $geodir_post_category_str;

	if ( ! empty( $geodir_post_category_str ) ) {
		$geodir_post_category_str = serialize( $geodir_post_category_str );
	}

	$all_var['post_category_array'] = html_entity_decode( (string) $geodir_post_category_str, ENT_QUOTES, 'UTF-8' );
	$script                         = "var post_category_array = " . json_encode( $all_var ) . ';';
	echo '<script>';
	echo $script;
	echo '</script>';

}

/**
 * Listing bounce map pin on hover script.
 *
 * @since 2.0.0
 *
 */
function geodir_listing_bounce_map_pin_on_hover() {
	if ( geodir_get_option( 'geodir_listing_hover_bounce_map_pin', true ) ) {
		?>
		<script>
			jQuery(function ($) {
				if (typeof(animate_marker) == 'function') {
					var groupTab = $("ul.geodir_category_list_view").children("li");
					groupTab.hover(function () {
						animate_marker('listing_map_canvas', String($(this).data("post-id")));
					}, function () {
						stop_marker_animation('listing_map_canvas', String($(this).data("post-id")));
					});
				} else {
					window.animate_marker = function () {
					};
					window.stop_marker_animation = function () {
					};
				}
			});
		</script>
		<?php
	}
}

add_action( 'geodir_after_listing_listview', 'geodir_listing_bounce_map_pin_on_hover', 10 );


/**
 * Search form submit button.
 *
 * @since 2.0.0
 */
function geodir_search_form_submit_button() {
	$default_search_button_label = geodir_get_option('search_default_button_text');
	if(!$default_search_button_label){$default_search_button_label = get_search_default_button_text();}



	/**
	 * Filter the default search button text value for the search form.
	 *
	 * This text can be changed via an option in settings, this is a last resort.
	 *
	 * @since 1.5.5
	 *
	 * @param string $default_search_button_label The current search button text.
	 */
	$default_search_button_label = apply_filters( 'geodir_search_default_search_button_text', $default_search_button_label );

	$fa_class = '';
	if ( strpos( $default_search_button_label, '#x' ) !== false ) {
		$fa_class = 'fa';
		$default_search_button_label = "&".$default_search_button_label;
	}


	?>
	<button class="geodir_submit_search <?php echo $fa_class; ?>" data-title="<?php esc_attr_e( $default_search_button_label ,'geodirectory'); ?>"><?php _e( $default_search_button_label ,'geodirectory'); ?></button>
	<?php
}

add_action( 'geodir_before_search_button', 'geodir_search_form_submit_button', 5000 );

/**
 * Search form post type input.
 *
 * @since 2.0.0
 */
function geodir_search_form_post_type_input() {
	global $geodir_search_post_type;
	$post_types     = apply_filters( 'geodir_search_form_post_types', geodir_get_posttypes( 'object' ) );
	$curr_post_type = $geodir_search_post_type;

	if ( ! empty( $post_types ) && count( (array) $post_types ) > 1 ) {

		foreach ( $post_types as $post_type => $info ){
			global $wpdb;
			$has_posts = $wpdb->get_row( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status='publish' LIMIT 1", $post_type ) );
			if ( ! $has_posts ) {
				unset($post_types->{$post_type});
			}
		}

		if ( ! empty( $post_types ) && count( (array) $post_types ) > 1 ) {

			$new_style = geodir_get_option( 'geodir_show_search_old_search_from' ) ? false : true;
			if ( $new_style ) {
				echo "<div class='gd-search-input-wrapper gd-search-field-cpt'>";
			}
			?>
			<select name="stype" class="search_by_post">
				<?php foreach ( $post_types as $post_type => $info ):
					global $wpdb;
					?>

					<option data-label="<?php echo get_post_type_archive_link( $post_type ); ?>"
					        value="<?php echo $post_type; ?>" <?php if ( isset( $_REQUEST['stype'] ) ) {
						if ( $post_type == $_REQUEST['stype'] ) {
							echo 'selected="selected"';
						}
					} elseif ( $curr_post_type == $post_type ) {
						echo 'selected="selected"';
					} ?>><?php _e( geodir_utf8_ucfirst( $info->labels->name ), 'geodirectory' ); ?></option>

				<?php endforeach; ?>
			</select>
			<?php
			if ( $new_style ) {
				echo "</div>";
			}
		}else{
			if(! empty( $post_types )){
				$pt_arr = (array)$post_types;
				echo '<input type="hidden" name="stype" value="' . key( $pt_arr  ) . '"  />';
			}else{
				echo '<input type="hidden" name="stype" value="gd_place"  />';
			}

		}

	}elseif ( ! empty( $post_types ) ) {
		echo '<input type="hidden" name="stype" value="gd_place"  />';
	}
}

/**
 * Search form search inputs.
 *
 * @since 2.0.0
 */
function geodir_search_form_search_input() {
	$default_search_for_text = geodir_get_option('search_default_text');
	if(!$default_search_for_text){$default_search_for_text = get_search_default_text();}
	?>
	<div class='gd-search-input-wrapper gd-search-field-search'>
		<input class="search_text" name="s"
		       value="<?php if ( isset( $_REQUEST['s'] ) && trim( $_REQUEST['s'] ) != '' ) {
			       echo esc_attr( stripslashes_deep( $_REQUEST['s'] ) );
		       } ?>" type="text"
		       onkeydown="javascript: if(event.keyCode == 13) geodir_click_search(this);"
		       placeholder="<?php esc_html_e($default_search_for_text,'geodirectory') ?>"
		/>
	</div>
	<?php
}

/**
 * Search form near inputs.
 *
 * @since 2.0.0
 */
function geodir_search_form_near_input() {

	$default_near_text = geodir_get_option('search_default_near_text');
	if(!$default_near_text){$default_near_text = get_search_default_near_text();}

	if ( isset( $_REQUEST['snear'] ) && $_REQUEST['snear'] != '' ) {
		$near = esc_attr( stripslashes_deep( $_REQUEST['snear'] ) );
	} else {
		$near = '';
	}
	

	global $geodir_search_post_type;
	$curr_post_type = $geodir_search_post_type;
	/**
	 * Used to hide the near field and other things.
	 *
	 * @since 1.6.9
	 * @param string $curr_post_type The current post type.
	 */
	$near_input_extra = apply_filters('geodir_near_input_extra','',$curr_post_type);


	/**
	 * Filter the "Near" text value for the search form.
	 *
	 * This is the input "value" attribute and can change depending on what the user searches and is not always the default value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $near              The current near value.
	 * @param string $default_near_text The default near value.
	 */
	$near = apply_filters( 'geodir_search_near_text', $near, $default_near_text );
	/**
	 * Filter the default "Near" text value for the search form.
	 *
	 * This is the default value if nothing has been searched.
	 *
	 * @since 1.0.0
	 *
	 * @param string $near              The current near value.
	 * @param string $default_near_text The default near value.
	 */
	$default_near_text = apply_filters( 'geodir_search_default_near_text', $default_near_text, $near );
	/**
	 * Filter the class for the near search input.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The class for the HTML near input, default is blank.
	 */
	$near_class = apply_filters( 'geodir_search_near_class', '' );


	echo "<div class='gd-search-input-wrapper gd-search-field-near' $near_input_extra>";
	do_action('geodir_before_near_input');
	?>
	<input name="snear" class="snear <?php echo $near_class; ?>" type="text" value="<?php echo $near; ?>"
	       onkeydown="javascript: if(event.keyCode == 13) geodir_click_search(this);" <?php echo $near_input_extra;?>
	       placeholder="<?php esc_html_e($default_near_text,'geodirectory') ?>"
	/>
	<?php
	do_action('geodir_after_near_input');
	echo "</div>";
}

add_action( 'geodir_search_form_inputs', 'geodir_search_form_post_type_input', 10 );
add_action( 'geodir_search_form_inputs', 'geodir_search_form_search_input', 20 );
add_action( 'geodir_search_form_inputs', 'geodir_search_form_near_input', 30 );

/**
 * Get search post type.
 *
 * @since 2.0.0
 *
 * @param string $pt Optional. Post type. Default null.
 * @return string $geodir_search_post_type.
 */
function geodir_get_search_post_type($pt=''){
	global $geodir_search_post_type;

	if($pt!=''){return $geodir_search_post_type = $pt;}
	if(!empty($geodir_search_post_type)){ return $geodir_search_post_type;}

	$geodir_search_post_type = geodir_get_current_posttype();

	if(!$geodir_search_post_type) {
		$geodir_search_post_type = geodir_get_default_posttype();
	}


	return $geodir_search_post_type;
}

/**
 * Search form.
 *
 * @since 2.0.0
 */
function geodir_search_form(){

	geodir_get_search_post_type();

	geodir_get_template_part('listing', 'filter-form');

	// Always die in functions echoing ajax content
	die();
}

add_action( 'wp_ajax_geodir_search_form', 'geodir_search_form' );
add_action( 'wp_ajax_nopriv_geodir_search_form', 'geodir_search_form' );

/**
 * Check wpml active or not.
 *
 * @since 1.5.0
 *
 * @return True if WPML is active else False.
 */
function geodir_is_wpml() {
    if (function_exists('icl_object_id')) {
        return true;
    }

    return false;
}

/**
 * Get the CPT that disabled review stars.
 *
 * @since 1.6.16
 *
 * @param string $post_type WP post type or WP texonomy. Ex: gd_place.
 * @param bool $taxonomy Whether $post_type is taxonomy or not.
 * @return bool True if review star disabled, otherwise false.
 */ 
function geodir_rating_disabled_post_types() {
	//$post_types = geodir_get_option( 'geodir_disable_rating_cpt' );

	$disabled = array();

	$post_types = geodir_get_posttypes('array');

	if(!empty($post_types )){
		foreach($post_types as $post_type => $val){
			if(isset($val['disable_reviews']) && $val['disable_reviews']){
				$disabled[] = $post_type;
			}
		}
	}
	
	
	/**
	 * Filter the post types array which have rating disabled.
	 *
	 * @since 1.6.16
	 *
	 * @param array $post_types Array of post types which have rating starts disabled.
	 */
	return apply_filters( 'geodir_rating_disabled_post_types', $disabled );
}

/**
 * Check review star disabled for certain CPT.
 *
 * @since 1.6.16
 *
 * @param string|int $post_type WP post type or Post ID or WP texonomy. Ex: gd_place.
 * @param bool $taxonomy Whether $post_type is taxonomy or not.
 * @return bool True if review star disabled, otherwise false.
 */ 
function geodir_cpt_has_rating_disabled( $post_type = '', $taxonomy = false ) {
	$post_types = $post_types = geodir_get_posttypes('array');
	if(isset($post_types[$post_type]['disable_reviews']) && $post_types[$post_type]['disable_reviews']){
		return true;
	}else{
		return false;
	}
}


/**
 * Check favourite disabled for certain CPT.
 *
 * @since 2.0.0
 *
 * @param string|int $post_type WP post type or Post ID or WP texonomy. Ex: gd_place.
 * @return bool True if review star disabled, otherwise false.
 */ 
function geodir_cpt_has_favourite_disabled( $post_type = '') {
	$post_types = $post_types = geodir_get_posttypes('array');
	if(isset($post_types[$post_type]['disable_favorites']) && $post_types[$post_type]['disable_favorites']){
		return true;
	}else{
		return false;
	}
}

/**
 * Checks that Yoast SEO is disabled on GD pages.
 *
 * @since 1.6.18
 *
 * @return bool True if Yoast SEO disabled on GD pages.
 */
function geodir_disable_yoast_seo_metas() {
    return (bool)geodir_get_option( 'geodir_disable_yoast_meta' );
}

/**
 * Get the listing view layout options array.
 *
 * @since 1.6.22
 *
 * @return array The listing view layout options.
 */
function geodir_listing_view_options() {
    $options = array(
        'gridview_onehalf' => __( 'Grid View (Two Columns)', 'geodirectory' ),
        'gridview_onethird' => __( 'Grid View (Three Columns)', 'geodirectory' ),
        'gridview_onefourth' => __( 'Grid View (Four Columns)', 'geodirectory' ),
        'gridview_onefifth' => __( 'Grid View (Five Columns)', 'geodirectory' ),
        'listview' => __( 'List view', 'geodirectory' ),
    );
    
    return apply_filters( 'geodir_listing_view_options', $options );
}

/**
 * Get the search page base url.
 *
 * @since 1.6.24
 *
 * @return string Filtered url.
 */
function geodir_search_page_base_url() {
    if ( function_exists( 'geodir_location_geo_home_link' ) ) {
        remove_filter( 'home_url', 'geodir_location_geo_home_link', 100000 );
    }

    $url = get_permalink(geodir_search_page_id());

    $url = trailingslashit( $url );

    if ( function_exists( 'geodir_location_geo_home_link' ) ) {
        add_filter( 'home_url', 'geodir_location_geo_home_link', 100000, 2 );
    }

    return apply_filters( 'geodir_search_page_base_url', $url );
}