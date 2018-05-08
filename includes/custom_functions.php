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
	$package_info['sendtofriend']     = 1;

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
	 * @type int $sendtofriend     Send to friend. Default 1.
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
 * Send Email to a friend.
 *
 * This function let the user to send Email to a friend.
 * Email content will be used WP Admin -> Geodirectory -> Notifications -> Other Emails -> Send to friend
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param array $request       {
 *                             The submitted form fields as an array.
 *
 * @type string $sendact       Enquiry type. Default "email_frnd".
 * @type string $pid           Post ID.
 * @type string $to_name       Friend name.
 * @type string $to_email      Friend email.
 * @type string $yourname      Sender name.
 * @type string $youremail     Sender email.
 * @type string $frnd_subject  Email subject.
 * @type string $frnd_comments Email Message.
 *
 * }
 */
function geodir_send_friend( $request ) {
	if ( ! GeoDir_Email::is_email_enabled( 'send_friend' ) ) {
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
	$data['from_name'] = ! empty( $request['yourname'] ) ? $request['yourname'] : '';
	$data['from_email'] = ! empty( $request['youremail'] ) ? $request['youremail'] : '';
	$data['subject'] = ! empty( $request['frnd_subject'] ) ? $request['frnd_subject'] : '';
	$data['comments'] = ! empty( $request['frnd_comments'] ) ? $request['frnd_comments'] : '';

	$allow = apply_filters( 'geodir_allow_send_to_friend_email', true, $gd_post, $data );
	if ( ! $allow ) {
		return false;
	}

	/**
	 * Send to friend email.
	 *
	 * @since 2.0.0
	 *
	 * @param object $gd_post The post object.
	 * @param array $data {
	 *	   The submitted form fields as an array.
	 *
	 * 	   @type string $friend_name   Friend name.
	 * 	   @type string $user_email    Friend email.
	 * 	   @type string $user_name     Sender name.
	 * 	   @type string $user_email    Sender email.
	 * 	   @type string $subject       Email subject.
	 *     @type string $comments      Email Message.
	 *
	 * }
	 */
	do_action( 'geodir_send_to_friend_email', $gd_post, $data );

	$redirect_to = add_query_arg( array( 'sendtofrnd' => 'success' ), get_permalink( $post_id ) );
	/**
	 * Filter redirect url after the send to friend email is sent.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url Redirect url.
	 */
	$redirect_to = apply_filters( 'geodir_send_to_friend_after_submit_redirect', $redirect_to );
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
 * Get WPML language code for current term.
 *
 * @since 1.5.0
 *
 * @global object $sitepress Sitepress WPML object.
 *
 * @param int $element_id Post ID or Term id.
 * @param string $element_type Element type. Ex: post_gd_place or tax_gd_placecategory.
 * @return Language code.
 */
function geodir_get_language_for_element($element_id, $element_type) {
    global $sitepress;

    return $sitepress->get_language_for_element($element_id, $element_type);
}

/**
 * Duplicate post details for WPML translation post.
 *
 * @since 1.5.0
 * @since 1.6.16 Sync reviews if sync comments allowed.
 *
 * @param int $master_post_id Original Post ID.
 * @param string $lang Language code for translating post.
 * @param array $postarr Array of post data.
 * @param int $tr_post_id Translation Post ID.
 * @param bool $after_save If true it will force duplicate. 
 *                         Added to fix duplicate translation for front end.
 */
function geodir_icl_make_duplicate($master_post_id, $lang, $postarr, $tr_post_id, $after_save = false) {
    global $sitepress;
    
    $post_type = get_post_type($master_post_id);
    $icl_ajx_action = !empty($_REQUEST['icl_ajx_action']) && $_REQUEST['icl_ajx_action'] == 'make_duplicates' ? true : false;
    if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'wpml_duplicate_dashboard' && !empty($_REQUEST['duplicate_post_ids'])) {
        $icl_ajx_action = true;
    }
    
    if (in_array($post_type, geodir_get_posttypes())) {
        if ($icl_ajx_action || $after_save) {
            // Duplicate post details
            geodir_icl_duplicate_post_details($master_post_id, $tr_post_id, $lang);
            
            // Duplicate taxonomies
            geodir_icl_duplicate_taxonomies($master_post_id, $tr_post_id, $lang);
            
            // Duplicate post images
            geodir_icl_duplicate_post_images($master_post_id, $tr_post_id, $lang);
        }
        
        // Sync post reviews
        if ($sitepress->get_setting('sync_comments_on_duplicates')) {
            geodir_wpml_duplicate_post_reviews($master_post_id, $tr_post_id, $lang);
        }
    }
}
add_filter( 'icl_make_duplicate', 'geodir_icl_make_duplicate', 11, 4 );

/**
 * Duplicate post listing manually after listing saved.
 *
 * @since 1.6.16 Sync reviews if sync comments allowed.
 *
 * @param int $post_id The Post ID.
 * @param string $lang Language code for translating post.
 * @param array $request_info The post details in an array.
 */
function geodir_wpml_duplicate_listing($post_id, $request_info) {
    global $sitepress;
    
    $icl_ajx_action = !empty($_REQUEST['icl_ajx_action']) && $_REQUEST['icl_ajx_action'] == 'make_duplicates' ? true : false;
    if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'wpml_duplicate_dashboard' && !empty($_REQUEST['duplicate_post_ids'])) {
        $icl_ajx_action = true;
    }
    
    if (!$icl_ajx_action && in_array(get_post_type($post_id), geodir_get_posttypes()) && $post_duplicates = $sitepress->get_duplicates($post_id)) {
        foreach ($post_duplicates as $lang => $dup_post_id) {
            geodir_icl_make_duplicate($post_id, $lang, $request_info, $dup_post_id, true);
        }
    }
}

/**
 * Duplicate post reviews for WPML translation post.
 *
 * @since 1.6.16
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $master_post_id Original Post ID.
 * @param int $tr_post_id Translation Post ID.
 * @param string $lang Language code for translating post.
 * @return bool True for success, False for fail.
 */
function geodir_wpml_duplicate_post_reviews($master_post_id, $tr_post_id, $lang) {
    global $wpdb;

    $reviews = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM " . GEODIR_REVIEW_TABLE . " WHERE post_id=%d ORDER BY comment_id ASC", $master_post_id), ARRAY_A);

    if (!empty($reviews)) {
        foreach ($reviews as $review) {
            geodir_wpml_duplicate_post_review($review['comment_id'], $master_post_id, $tr_post_id, $lang);
        }
    }

    return false;
}

/**
 * Duplicate post general details for WPML translation post.
 *
 * @since 1.5.0
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int $master_post_id Original Post ID.
 * @param int $tr_post_id Translation Post ID.
 * @param string $lang Language code for translating post.
 * @return bool True for success, False for fail.
 */
function geodir_icl_duplicate_post_details($master_post_id, $tr_post_id, $lang) {
    global $wpdb, $plugin_prefix;

    $post_type = get_post_type($master_post_id);
    $post_table = $plugin_prefix . $post_type . '_detail';

    $query = $wpdb->prepare("SELECT * FROM " . $post_table . " WHERE post_id = %d", array($master_post_id));
    $data = (array)$wpdb->get_row($query);

    if ( !empty( $data ) ) {
        $data['post_id'] = $tr_post_id;
        unset($data['default_category'], $data['marker_json'], $data['featured_image'], $data[$post_type . 'category']);
        $wpdb->update($post_table, $data, array('post_id' => $tr_post_id));
        return true;
    }

    return false;
}

/**
 * Duplicate post taxonomies for WPML translation post.
 *
 * @since 1.5.0
 *
 * @global object $sitepress Sitepress WPML object.
 * @global object $wpdb WordPress Database object.
 *
 * @param int $master_post_id Original Post ID.
 * @param int $tr_post_id Translation Post ID.
 * @param string $lang Language code for translating post.
 * @return bool True for success, False for fail.
 */
function geodir_icl_duplicate_taxonomies($master_post_id, $tr_post_id, $lang) {
    global $sitepress, $wpdb;
    $post_type = get_post_type($master_post_id);

    remove_filter('get_term', array($sitepress,'get_term_adjust_id')); // AVOID filtering to current language

    $taxonomies = get_object_taxonomies($post_type);
    foreach ($taxonomies as $taxonomy) {
        $terms = get_the_terms($master_post_id, $taxonomy);
        $terms_array = array();
        
        if ($terms) {
            foreach ($terms as $term) {
                $tr_id = apply_filters( 'translate_object_id',$term->term_id, $taxonomy, false, $lang);
                
                if (!is_null($tr_id)){
                    // not using get_term - unfiltered get_term
                    $translated_term = $wpdb->get_row($wpdb->prepare("
                        SELECT * FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} x ON x.term_id = t.term_id WHERE t.term_id = %d AND x.taxonomy = %s", $tr_id, $taxonomy));

                    $terms_array[] = $translated_term->term_id;
                }
            }

            if (!is_taxonomy_hierarchical($taxonomy)){
                $terms_array = array_unique( array_map( 'intval', $terms_array ) );
            }

            wp_set_post_terms($tr_post_id, $terms_array, $taxonomy);
            
            if ($taxonomy == $post_type . 'category') {
                geodir_set_postcat_structure($tr_post_id, $post_type . 'category');
            }
        }
    }
}

/**
 * Duplicate post images for WPML translation post.
 *
 * @since 1.5.0
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $master_post_id Original Post ID.
 * @param int $tr_post_id Translation Post ID.
 * @param string $lang Language code for translating post.
 * @return bool True for success, False for fail.
 */
function geodir_icl_duplicate_post_images($master_post_id, $tr_post_id, $lang) {
    global $wpdb;

    $query = $wpdb->prepare("DELETE FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE mime_type like %s AND post_id = %d", array('%image%', $tr_post_id));
    $wpdb->query($query);

    $query = $wpdb->prepare("SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE mime_type like %s AND post_id = %d ORDER BY menu_order ASC", array('%image%', $master_post_id));
    $post_images = $wpdb->get_results($query);

    if ( !empty( $post_images ) ) {
        foreach ( $post_images as $post_image) {
            $image_data = (array)$post_image;
            unset($image_data['ID']);
            $image_data['post_id'] = $tr_post_id;
            
            $wpdb->insert(GEODIR_ATTACHMENT_TABLE, $image_data);
            
            geodir_set_wp_featured_image($tr_post_id);
        }
        
        return true;
    }

    return false;
}


/**
 * Duplicate post review for WPML translation post.
 *
 * @since 1.6.16
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int $master_comment_id Original Comment ID.
 * @param int $master_post_id Original Post ID.
 * @param int $tr_post_id Translation Post ID.
 * @param string $lang Language code for translating post.
 * @return bool True for success, False for fail.
 */
function geodir_wpml_duplicate_post_review($master_comment_id, $master_post_id, $tr_post_id, $lang) {
    global $wpdb, $plugin_prefix, $sitepress;

    $review = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . GEODIR_REVIEW_TABLE . " WHERE comment_id=%d ORDER BY comment_id ASC", $master_comment_id), ARRAY_A);

    if (empty($review)) {
        return false;
    }
    if ($review['post_id'] != $master_post_id) {
        $wpdb->query($wpdb->prepare("UPDATE " . GEODIR_REVIEW_TABLE . " SET post_id=%d WHERE comment_id=%d", $master_post_id, $master_comment_id));
        GeoDir_Comments::update_post_rating($master_post_id, $post_type);
    }

    $tr_comment_id = geodir_wpml_duplicate_comment_exists($tr_post_id, $master_comment_id);

    if (empty($tr_comment_id)) {
        return false;
    }

    $post_type = get_post_type($master_post_id);
    $post_table = $plugin_prefix . $post_type . '_detail';

    $translated_post = $wpdb->get_row($wpdb->prepare("SELECT latitude, longitude, city, region, country FROM " . $post_table . " WHERE post_id = %d", $tr_post_id), ARRAY_A);
    if (empty($translated_post)) {
        return false;
    }

    $review['comment_id'] = $tr_comment_id;
    $review['post_id'] = $tr_post_id;
    $review['city'] = $translated_post['city'];
    $review['region'] = $translated_post['region'];
    $review['country'] = $translated_post['country'];
    $review['latitude'] = $translated_post['latitude'];
    $review['longitude'] = $translated_post['longitude'];

    $tr_review_id = $wpdb->get_var($wpdb->prepare("SELECT comment_id FROM " . GEODIR_REVIEW_TABLE . " WHERE comment_id=%d AND post_id=%d ORDER BY comment_id ASC", $tr_comment_id, $tr_post_id));

    if ($tr_review_id) { // update review
        $wpdb->update(GEODIR_REVIEW_TABLE, $review, array('comment_id' => $tr_review_id));
    } else { // insert review
        $wpdb->insert(GEODIR_REVIEW_TABLE, $review);
        $tr_review_id = $wpdb->insert_id;
    }

    if ($tr_post_id) {
        GeoDir_Comments::update_post_rating($tr_post_id, $post_type);
        
        if (defined('GEODIRREVIEWRATING_VERSION') && geodir_get_option('geodir_reviewrating_enable_review') && $sitepress->get_setting('sync_comments_on_duplicates')) {
            $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_COMMENTS_REVIEWS_TABLE . " WHERE comment_id = %d", array($tr_comment_id)));
            $likes = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_COMMENTS_REVIEWS_TABLE . " WHERE comment_id=%d ORDER BY like_date ASC", $master_comment_id, $tr_post_id), ARRAY_A);

            if (!empty($likes)) {
                foreach ($likes as $like) {
                    unset($like['like_id']);
                    $like['comment_id'] = $tr_comment_id;
                    
                    $wpdb->insert(GEODIR_COMMENTS_REVIEWS_TABLE, $like);
                }
            }
        }
    }

    return $tr_review_id;
}

/**
 * Synchronize review for WPML translation post.
 *
 * @since 1.6.16
 *
 * @global object $wpdb WordPress Database object.
 * @global object $sitepress Sitepress WPML object.
 * @global array $gd_wpml_posttypes Geodirectory post types array.
 *
 * @param int $comment_id The Comment ID.
 */
function gepdir_wpml_sync_comment($comment_id) {
    global $wpdb, $sitepress, $gd_wpml_posttypes;

    if (empty($gd_post_types)) {
        $gd_wpml_posttypes = geodir_get_posttypes();
    }

    $comment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->comments} WHERE comment_ID=%d", $comment_id), ARRAY_A);
    if (empty($comment)) {
        return;
    }

    $post_id = $comment['comment_post_ID'];
    $post_type = $post_id ? get_post_type($post_id) : NULL;

    if (!($post_type && in_array($post_type, $gd_wpml_posttypes))) {
        return;
    }

    $post_duplicates = $sitepress->get_duplicates($post_id);
    if (empty($post_duplicates)) {
        return;
    }

    foreach ($post_duplicates as $lang => $dup_post_id) {
        if (empty($comment['comment_parent'])) {
            geodir_wpml_duplicate_post_review($comment_id, $post_id, $dup_post_id, $lang);
        }
    }
    
    return true;
}

/**
 * Get the WPML duplicate comment ID of the comment.
 *
 * @since 1.6.16
 *
 * @global object $dup_post_id WordPress Database object.
 *
 * @param int $dup_post_id The duplicate post ID.
 * @param int $original_cid The original Comment ID.
 * @return int The duplicate comment ID.
 */
function geodir_wpml_duplicate_comment_exists($dup_post_id, $original_cid) {
    global $wpdb;

    $duplicate = $wpdb->get_var(
        $wpdb->prepare(
            "   SELECT comm.comment_ID
                FROM {$wpdb->comments} comm
                JOIN {$wpdb->commentmeta} cm
                    ON comm.comment_ID = cm.comment_id
                WHERE comm.comment_post_ID = %d
                    AND cm.meta_key = '_icl_duplicate_of'
                    AND cm.meta_value = %d
                LIMIT 1",
            $dup_post_id,
            $original_cid
        )
    );

    return $duplicate;
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
 * Checks the user allowed to duplicate listing or not for WPML.
 *
 * @since 1.6.18
 *
 * @param int $post_id The post ID.
 * @return bool True if allowed.
 */
function geodir_wpml_allowed_to_duplicate( $post_id ) {
    $allowed = false;
    
    if ( !geodir_is_wpml() || empty( $post_id ) ) {
        return $allowed;
    }
    
    $user_id = (int)get_current_user_id();
    
    if ( empty( $user_id ) ) {
        return $allowed;
    }
    
    $post_type = get_post_type( $post_id );
    if ( !geodir_wpml_is_post_type_translated( $post_type ) || get_post_meta( $post_id, '_icl_lang_duplicate_of', true ) ) {
        return $allowed;
    }
    
    if ( geodir_listing_belong_to_current_user( $post_id ) ) {
        $allowed = true;
    }
    
    $disable_cpts = geodir_get_option( 'geodir_wpml_disable_duplicate' );
    if ( $allowed && !empty( $disable_cpts ) && in_array( $post_type, $disable_cpts ) ) {
        $allowed = false;
    }
    
    /**
     * Filter the user allowed to duplicate listing or not for WPML.
     *
     * @param bool $allowed True if allowed.
     * @param int $post_id The post ID.
     */
    return apply_filters( 'geodir_wpml_allowed_to_duplicate', $allowed, $post_id );
}

/**
 * Display WPML languages option in sidebar to allow authors to duplicate their listing.
 *
 * @since 1.6.18
 *
 * @global WP_Post|null $post The current post.
 * @global bool $preview True if the current page is add listing preview page. False if not.
 * @global object $sitepress Sitepress WPML object.
 *
 * @param string $content_html The output html of the geodir_edit_post_link() function.
 * @return string Filtered html of the geodir_edit_post_link() function.
 */
function geodir_wpml_frontend_duplicate_listing( $content_html ) {
    global $post, $preview, $sitepress;
    
    if ( !empty( $post->ID ) && !$preview && geodir_is_page( 'detail' ) && geodir_wpml_allowed_to_duplicate( $post->ID ) ) {
        $post_id = $post->ID;
        $element_type = 'post_' . get_post_type( $post_id );
        $original_post_id = $sitepress->get_original_element_id( $post_id, $element_type );
        
        if ( $original_post_id == $post_id ) {
            $wpml_languages = $sitepress->get_active_languages();
            $post_language = $sitepress->get_language_for_element( $post_id, $element_type );
            
            if ( !empty( $wpml_languages ) && isset( $wpml_languages[ $post_language ] ) ) {
                unset( $wpml_languages[ $post_language ] );
            }
            
            if ( !empty( $wpml_languages ) ) {
                $trid  = $sitepress->get_element_trid( $post_id, $element_type );
                $element_translations = $sitepress->get_element_translations( $trid, $element_type );
                $duplicates = $sitepress->get_duplicates( $post_id );
                
                $wpml_content = '<div class="geodir-company_info gd-detail-duplicate"><h3 class="widget-title">' . __( 'Translate Listing', 'geodirectory' ) . '</h3>';
                $wpml_content .= '<table class="gd-duplicate-table" style="width:100%;margin:0"><tbody>';
                $wpml_content .= '<tr style="border-bottom:solid 1px #efefef"><th style="padding:0 2px 2px 2px">' . __( 'Language', 'geodirectory' ) . '</th><th style="width:25px;"></th><th style="width:5em;text-align:center">' . __( 'Translate', 'geodirectory' ) . '</th></tr>';
                
                $needs_translation = false;
                
                foreach ( $wpml_languages as $lang_code => $lang ) {
                    $duplicates_text = '';
                    $translated = false;
                    
                    if ( !empty( $element_translations ) && isset( $element_translations[$lang_code] ) ) {
                        $translated = true;
                        
                        if ( !empty( $duplicates ) && isset( $duplicates[$lang_code] ) ) {
                            $duplicates_text = ' ' . __( '(duplicate)', 'geodirectory' );
                        }
                    } else {
                        $needs_translation = true;
                    }
                    
                    $wpml_content .= '<tr><td style="padding:4px">' . $lang['english_name'] . $duplicates_text . '</td><td>&nbsp;</td><td style="text-align:center;">';
                    
                    if ( $translated ) {
                        $wpml_content .= '<i class="fa fa-check" style="color:orange"></i>';
                    } else {
                        $wpml_content .= '<input name="gd_icl_dup[]" value="' . $lang_code . '" title="' . esc_attr__( 'Create duplicate', 'geodirectory' ) . '" type="checkbox">';
                    }
                    
                    $wpml_content .= '</td></tr>';
                }
                
                if ( $needs_translation ) {
                    $nonce = wp_create_nonce( 'geodir_duplicate_nonce' );
                    $wpml_content .= '<tr><td>&nbsp;</td><td style="vertical-align:middle;padding-top:13px"><i style="display:none" class="fa fa-spin fa-refresh"></i></td><td style="padding:15px 3px 3px 3px;text-align:right"><button data-nonce="' . esc_attr( $nonce ) . '" data-post-id="' . $post_id . '" id="gd_make_duplicates" class="button-secondary">' . __( 'Duplicate', 'geodirectory' ) . '</button></td></tr>';
                }
                
                $wpml_content .= '</tbody></table>';
                $wpml_content .= '</div>';
                
                $content_html .= $wpml_content;
            }
        }
    }
    
    return $content_html;
}

/**
 * Add setting for WPML front-end duplicate translation in design page setting section.
 *
 * @since 1.6.18
 *
 * @param array $settings GD design settings array.
 * @return array Filtered GD design settings array..
 */
function geodir_wpml_duplicate_settings( $settings = array() ) {
    $new_settings = array();
    
    foreach ( $settings as $key => $setting ) {
        
        if ( isset( $setting['type'] ) && $setting['type'] == 'sectionend' && $setting['id'] == 'detail_page_settings' ) {
            $new_settings[] = array(
                'name' => __('Disable WPML duplicate translation', 'geodirectory'),
                'desc' => __('Select post types to disable front end WPML duplicate translation. For selected post types the WPML duplicate option will be disabled from listing detail page sidebar.', 'geodirectory'),
                'tip' => '',
                'id' => 'geodir_wpml_disable_duplicate',
                'css' => 'min-width:300px;',
                'std' => '',
                'type' => 'multiselect',
                'placeholder_text' => __('Select post types', 'geodirectory'),
                'class' => 'geodir-select',
                'options' => geodir_post_type_options()
            );
        }
        $new_settings[] = $setting;
    }
    
    return $new_settings;
}

/**
 * Checks if a given taxonomy is currently translated.
 *
 * @since 1.6.22
 *
 * @param string $taxonomy name/slug of a taxonomy.
 * @return bool true if the taxonomy is currently set to being translatable in WPML.
 */
function geodir_wpml_is_taxonomy_translated( $taxonomy ) {
    if ( empty( $taxonomy ) || !geodir_is_wpml() || !function_exists( 'is_taxonomy_translated' ) ) {
        return false;
    }
    
    if ( is_taxonomy_translated( $taxonomy ) ) {
        return true;
    }
    
    return false;
}

/**
 * Checks if a given post_type is currently translated.
 *
 * @since 1.6.22
 *
 * @param string $post_type name/slug of a post_type.
 * @return bool true if the post_type is currently set to being translatable in WPML.
 */
function geodir_wpml_is_post_type_translated( $post_type ) {
    if ( empty( $post_type ) || !geodir_is_wpml() || !function_exists( 'is_post_type_translated' ) ) {
        return false;
    }
    
    if ( is_post_type_translated( $post_type ) ) {
        return true;
    }
    
    return false;
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